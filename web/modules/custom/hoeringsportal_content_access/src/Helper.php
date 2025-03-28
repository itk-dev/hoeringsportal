<?php

namespace Drupal\hoeringsportal_content_access;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Template\Attribute;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views\ViewExecutable;

/**
 * Content access helper.
 */
final class Helper {
  use StringTranslationTrait;

  private const FIELD_DEPARTMENT = 'field_department';

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private NodeStorageInterface $nodeStorage;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private UserStorageInterface $userStorage;

  /**
   * The term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  private TermStorageInterface $termStorage;

  /**
   * The user departments.
   *
   * @var array
   */
  private array $userDepartments;

  /**
   * The settings.
   *
   * @var \Drupal\hoeringsportal_content_access\Settings
   */
  private Settings $settings;

  /**
   * The bundles that must be checked for department access.
   *
   * @var array|string[]
   */
  private array $departmentBundles = [
    'hearing',
    'public_meeting',
  ];

  /**
   * Constructor.
   */
  public function __construct(
    private readonly AccountInterface $currentUser,
    private readonly RouteMatchInterface $routeMatch,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->settings = new Settings();
  }

  /**
   * Implements hook_node_access().
   */
  public function nodeAccess(NodeInterface $node, string $operation, AccountInterface $account): AccessResultInterface {
    $bundle = $node->bundle();
    if ('project' === $bundle && 'view' === $operation) {
      return new AccessResultForbidden();
    }
    if (in_array($bundle, $this->departmentBundles, TRUE)) {
      return $this->checkDepartmentAccess($node, $operation, $account);
    }

    return new AccessResultNeutral();
  }

  /**
   * Implements hook_views_query_alter().
   *
   * Alters query to only select nodes that the current user has edit access to.
   */
  public function viewsQueryAlter(ViewExecutable $view, QueryPluginBase $query) {
    if ($this->bypassDepartmentAccessCheck()) {
      return;
    }

    if ('content' === $view->id() && $query instanceof Sql) {
      $nodeIds = $this->getUserDepartmentNodeIds();
      $query->addWhere(0, 'nid', $nodeIds ?: [0], 'IN');
    }
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter() for node_form.
   */
  public function nodeFormAlter(array &$form, FormStateInterface $formState, string $formId) {
    // Remove the department field if no departments are available.
    if (isset($form[self::FIELD_DEPARTMENT]['widget']['#options'])
      && empty($form[self::FIELD_DEPARTMENT]['widget']['#options'])
    ) {
      $form[self::FIELD_DEPARTMENT]['#access'] = FALSE;
    }

    if ($this->bypassDepartmentAccessCheck()) {
      return;
    }

    // Set all user's departments as departments on new nodes.
    if ($this->isAddingNode()) {
      if (isset($form[self::FIELD_DEPARTMENT])) {
        $userDepartments = $this->getUserDepartments();
        $form[self::FIELD_DEPARTMENT]['widget']['#default_value'] = $userDepartments;
      }
    }

    // Validate departments when creating and editing nodes.
    if ($this->isEditingNode()) {
      if (isset($form[self::FIELD_DEPARTMENT])) {
        $form['#validate'][] = $this->validateDepartment(...);
      }
    }
  }

  /**
   * Implements template_preprocess_form_element().
   *
   * Hides departments the current user does not have access to.
   */
  public function preprocessFormElement(&$variables) {
    if ($this->bypassDepartmentAccessCheck()) {
      return;
    }

    $userDepartments = $this->getUserDepartments();

    // Make sure that user can only see its own departments.
    if ($this->isEditingNode()) {
      if ('checkbox' === ($variables['element']['#type'] ?? NULL)
        && self::FIELD_DEPARTMENT === ($variables['element']['#array_parents'][0] ?? NULL)) {
        $value = (string) $variables['element']['#return_value'];
        if (!in_array($value, $userDepartments, TRUE)) {
          // Remove inaccessible element from display.
          $variables['label_display'] = 'none';
          unset($variables['children']);
          $element = $variables['element'];
          // Make sure that selected values are persisted by rendering a hidden
          // field for selected values.
          if ($element['#checked'] ?? FALSE) {
            $attributes = $element['#attributes'];
            $variables['children'] = new FormattableMarkup(sprintf('<input %s/>', new Attribute([
              'type' => 'hidden',
              'name' => $attributes['name'],
              'value' => $attributes['value'],
            ])), []);
          }
        }
      }
    }
  }

  /**
   * Check that user has selected at least one of its own departments.
   */
  private function validateDepartment(array &$form, FormStateInterface $formState) {
    if ($this->bypassDepartmentAccessCheck()) {
      return;
    }

    $departments = array_column($formState->getValue(self::FIELD_DEPARTMENT) ?? [], 'target_id');
    $userDepartments = $this->getUserDepartments();
    if (empty(array_intersect($departments, $userDepartments))) {
      $formState->setErrorByName(self::FIELD_DEPARTMENT, $this->t('You must select at least one of your departments'));
    }
  }

  /**
   * Check department access.
   *
   * Only "update" and "delete" access is checked, and access is granted if and
   * only if the account has a non-empty list of departments and the node
   * belongs to at least one of these departments.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param string $operation
   *   The operation.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The result.
   */
  private function checkDepartmentAccess(NodeInterface $node, string $operation, AccountInterface $account): AccessResultInterface {
    if (!$this->bypassDepartmentAccessCheck()) {
      if (in_array($operation, ['update', 'delete'], TRUE)) {
        $userDepartments = $this->getUserDepartments($account);
        if (empty($userDepartments)
          || empty(array_intersect($userDepartments,
            $this->getDepartments($node)))) {
          return new AccessResultForbidden();
        }
      }
    }

    return new AccessResultNeutral();
  }

  /**
   * Get department (IDs).
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $entity
   *   The entity.
   *
   * @return array
   *   The department IDs.
   */
  private function getDepartments(ContentEntityBase $entity): array {
    if ($entity->hasField(self::FIELD_DEPARTMENT)) {
      $department = $entity->get(self::FIELD_DEPARTMENT);
      if ($department instanceof EntityReferenceFieldItemListInterface) {
        return array_column($department->getValue(), 'target_id');
      }
    }

    return [];
  }

  /**
   * Get departments for an account.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account.
   */
  private function getUserDepartments(?AccountInterface $account = NULL): array {
    $account ??= $this->currentUser;
    if (empty($this->userDepartments[$account->id()])) {
      $user = $this->userStorage->load($account->id());
      $this->userDepartments[$account->id()] = $this->getDepartments($user);
    }

    return $this->userDepartments[$account->id()];
  }

  /**
   * Get list of node IDs an account has edit access to.
   */
  private function getUserDepartmentNodeIds(?AccountInterface $account = NULL): array {
    $account ??= $this->currentUser;
    $departments = $this->getUserDepartments($account);
    $query = $this->nodeStorage->getQuery();

    return $query
      ->accessCheck(TRUE)
      ->condition($query->orConditionGroup()
        ->condition('type', $this->departmentBundles, 'NOT IN')
        ->condition(self::FIELD_DEPARTMENT, $departments ?: [0], 'IN')
      )
      ->execute();
  }

  /**
   * Check if a node is being added.
   */
  private function isAddingNode() {
    return 'node.add' === $this->routeMatch->getRouteName();
  }

  /**
   * Check if a node is being edited.
   */
  private function isEditingNode() {
    return $this->isAddingNode()
      || 'entity.node.edit_form' === $this->routeMatch->getRouteName();
  }

  /**
   * Check if department access check must be bypassed.
   */
  private function bypassDepartmentAccessCheck(): bool {
    return $this->settings->bypassDepartmentAccessCheck()
      || $this->currentUser->hasPermission('bypass node access');
  }

  /**
   * Implements hook_openid_connect_userinfo_save().
   */
  public function userinfoSave(UserInterface $account, array $context) {
    // @todo Get these values from settings?
    $vocabularyId = $this->settings->getDepartmentVocabularyId();
    $claim = $this->settings->getUserDepartmentClaim();
    if (isset($context['user_data'][$claim])) {
      $terms = $this->termStorage->loadTree($vocabularyId);
      // Exchange to IDs to real term entities.
      $terms = $this->termStorage->loadMultiple(array_column($terms, 'tid'));
      $values = (array) $context['user_data'][$claim];
      /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $departmentField */
      $departmentField = $account->get(self::FIELD_DEPARTMENT);
      // Set terms on user.
      $departmentField->setValue(
        array_filter($terms, static fn (TermInterface $term) => in_array(
          $term->get('field_claim_value')->getString() ?: $term->getName(),
          $values
        ))
      );
    }
  }

}
