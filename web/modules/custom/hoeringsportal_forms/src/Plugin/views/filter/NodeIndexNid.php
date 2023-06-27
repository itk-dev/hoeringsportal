<?php

namespace Drupal\hoeringsportal_forms\Plugin\views\filter;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorageInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter by node id.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("node_index_nid")
 */
class NodeIndexNid extends ManyToOne {

  /**
   * NodeType storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeTypeStorage;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * Constructs a NodeIndexNid object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $node_type_storage
   *   The node storage.
   * @param \Drupal\node\NodeStorageInterface $node_storage
   *   The node storage.
   * @param \Drupal\Core\Entity\EntityRepository $entity_repository
   *   The entity manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $node_type_storage, NodeStorageInterface $node_storage, EntityRepository $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeTypeStorage = $node_type_storage;
    $this->nodeStorage = $node_storage;
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('node_type'),
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    if (!empty($this->definition['node'])) {
      $this->options['bundle'] = $this->definition['node'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasExtraOptions() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    return $this->valueOptions;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['type'] = ['default' => 'textfield'];
    $options['limit'] = ['default' => TRUE];
    $options['bundle'] = ['default' => ''];
    $options['error_message'] = ['default' => TRUE];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    $bundles = $this->nodeTypeStorage->loadMultiple();
    $options = [];
    foreach ($bundles as $bundle) {
      $options[$bundle->id()] = $bundle->label();
    }

    if ($this->options['limit']) {
      // We only do this when the form is displayed.
      if (empty($this->options['bundle'])) {
        $first_bundle = reset($bundles);
        $this->options['bundle'] = $first_bundle->id();
      }

      if (empty($this->definition['bundle'])) {
        $form['bundle'] = [
          '#type' => 'radios',
          '#title' => $this->t('Bundle'),
          '#options' => $options,
          '#description' => $this->t('Select which bundle to show nodes for in the regular options.'),
          '#default_value' => $this->options['bundle'],
        ];
      }
    }

    $form['type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Selection type'),
      '#options' => [
        'select' => $this->t('Dropdown'),
        'textfield' => $this->t('Autocomplete'),
      ],
      '#default_value' => $this->options['type'],
    ];

  }

  /**
   * Define form a new form.
   *
   * @param mixed $form
   *   The new admin form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $bundle = $this->nodeTypeStorage->load($this->options['bundle']);
    if (empty($bundle) && $this->options['limit']) {
      $form['markup'] = [
        '#markup' => '<div class="js-form-item form-item">' . $this->t('An invalid type is selected. Please change it in the options.') . '</div>',
      ];
      return;
    }

    if ($this->options['type'] == 'textfield') {
      $nodes = $this->value ? Node::loadMultiple(($this->value)) : [];
      $form['value'] = [
        '#title' => $this->options['limit'] ? $this->t('Select nodes from bundle @bundle', ['@bundle' => $bundle->label()]) : $this->t('Select content'),
        '#type' => 'textfield',
        '#default_value' => EntityAutocomplete::getEntityLabels($nodes),
      ];

      if ($this->options['limit']) {
        $form['value']['#type'] = 'entity_autocomplete';
        $form['value']['#target_type'] = 'node';
        $form['value']['#selection_settings']['target_bundles'] = [$bundle->id()];
        $form['value']['#tags'] = TRUE;
        $form['value']['#process_default_value'] = FALSE;
      }
    }
    else {
      $options = [];
      $query = \Drupal::entityQuery('node')
        // @todo Sorting on bundle properties -
        ->sort('title')
        ->addTag('node_access');
      if ($this->options['limit']) {
        $query->condition('type', $bundle->id());
      }
      $nodes = Node::loadMultiple($query->execute());
      foreach ($nodes as $node) {
        $options[$node->id()] = $this->entityRepository->getTranslationFromContext($node)->label();
      }

      $default_value = (array) $this->value;

      if ($exposed = $form_state->get('exposed')) {
        $identifier = $this->options['expose']['identifier'];

        if (!empty($this->options['expose']['reduce'])) {
          $options = $this->reduceValueOptions($options);

          if (!empty($this->options['expose']['multiple']) && empty($this->options['expose']['required'])) {
            $default_value = [];
          }
        }

        if (empty($this->options['expose']['multiple'])) {
          if (empty($this->options['expose']['required']) && (empty($default_value) || !empty($this->options['expose']['reduce']))) {
            $default_value = 'All';
          }
          elseif (empty($default_value)) {
            $keys = array_keys($options);
            $default_value = array_shift($keys);
          }
          // Due to #1464174 there is a chance that array('')
          // was saved in the admin ui.
          // Let's choose a safe default value.
          elseif ($default_value == ['']) {
            $default_value = 'All';
          }
          else {
            $copy = $default_value;
            $default_value = array_shift($copy);
          }
        }
      }
      $form['value'] = [
        '#type' => 'select',
        '#title' => $this->options['limit'] ? $this->t('Select nodes from bundle @bundle', ['@bundle' => $bundle->label()]) : $this->t('Select content'),
        '#multiple' => TRUE,
        '#options' => $options,
        '#size' => min(9, count($options)),
        '#default_value' => $default_value,
      ];

      $user_input = $form_state->getUserInput();
      if ($exposed && isset($identifier) && !isset($user_input[$identifier])) {
        $user_input[$identifier] = $default_value;
        $form_state->setUserInput($user_input);
      }
    }

    if (!$form_state->get('exposed')) {
      // Retain the helper option.
      $this->helper->buildOptionsForm($form, $form_state);

      // Show help text if not exposed to end users.
      $form['value']['#description'] = t('Leave blank for all. Otherwise, the first selected term will be the default instead of "Any".');
    }
  }

  /**
   * Validate the admin form.
   *
   * @param mixed $form
   *   The form to validate.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   */
  protected function valueValidate($form, FormStateInterface $form_state) {
    // We only validate if they've chosen the text field style.
    if ($this->options['type'] != 'textfield') {
      return;
    }

    $tids = [];
    if ($values = $form_state->getValue(['options', 'value'])) {
      foreach ($values as $value) {
        $tids[] = $value['target_id'];
      }
    }
    $form_state->setValue(['options', 'value'], $tids);
  }

  /**
   * Whether to accept exposed input.
   *
   * @param mixed $input
   *   The input.
   *
   * @return bool
   *   Whether to accept the exposed form input.
   */
  public function acceptExposedInput($input) {
    if (empty($this->options['exposed'])) {
      return TRUE;
    }
    // We need to know the operator, which is normally set in
    // \Drupal\views\Plugin\views\filter\FilterPluginBase::acceptExposedInput(),
    // before we actually call the parent version of ourselves.
    if (!empty($this->options['expose']['use_operator']) && !empty($this->options['expose']['operator_id']) && isset($input[$this->options['expose']['operator_id']])) {
      $this->operator = $input[$this->options['expose']['operator_id']];
    }

    // If view is an attachment and is inheriting exposed filters, then assume
    // exposed input has already been validated.
    if (!empty($this->view->is_attachment) && $this->view->display_handler->usesExposed()) {
      $this->validated_exposed_input = (array) $this->view->exposed_raw_input[$this->options['expose']['identifier']];
    }

    // If we're checking for EMPTY or NOT, we don't need any input, and we can
    // say that our input conditions are met by just having the right operator.
    if ($this->operator == 'empty' || $this->operator == 'not empty') {
      return TRUE;
    }

    // If it's non-required and there's no value don't bother filtering.
    if (!$this->options['expose']['required'] && empty($this->validated_exposed_input)) {
      return FALSE;
    }

    $rc = parent::acceptExposedInput($input);
    if ($rc) {
      // If we have previously validated input, override.
      if (isset($this->validated_exposed_input)) {
        $this->value = $this->validated_exposed_input;
      }
    }

    return $rc;
  }

  /**
   * Custom validation for the exposed form.
   *
   * @param mixed $form
   *   The exposed form to validate.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the exposed form.
   */
  public function validateExposed(&$form, FormStateInterface $form_state) {
    if (empty($this->options['exposed'])) {
      return;
    }

    $identifier = $this->options['expose']['identifier'];

    // We only validate if they've chosen the text field style.
    if ($this->options['type'] != 'textfield') {
      if ($form_state->getValue($identifier) != 'All') {
        $this->validated_exposed_input = (array) $form_state->getValue($identifier);
      }
      return;
    }

    if (empty($this->options['expose']['identifier'])) {
      return;
    }

    if ($values = $form_state->getValue($identifier)) {
      foreach ($values as $value) {
        $this->validated_exposed_input[] = $value['target_id'];
      }
    }
  }

  /**
   * Custom submit function.
   *
   * @param mixed $form
   *   The form of the submit.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   */
  protected function valueSubmit($form, FormStateInterface $form_state) {
    // Prevent array_filter from messing up our arrays in parent submit.
  }

  /**
   * The exposed form builder.
   *
   * @param mixed $form
   *   The exposed form to build.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   */
  public function buildExposeForm(&$form, FormStateInterface $form_state) {
    parent::buildExposeForm($form, $form_state);
    if ($this->options['type'] != 'select') {
      unset($form['expose']['reduce']);
    }
    $form['error_message'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display error message'),
      '#default_value' => !empty($this->options['error_message']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    // Set up $this->valueOptions for the parent summary.
    $this->valueOptions = [];

    if ($this->value) {
      $this->value = array_filter($this->value);
      $nodes = Node::loadMultiple($this->value);
      foreach ($nodes as $node) {
        $this->valueOptions[$node->id()] = $this->entityRepository->getTranslationFromContext($node)->label();
      }
    }
    return parent::adminSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();
    // The result potentially depends on term access and so is just cacheable
    // per user.
    // @todo See https://www.drupal.org/node/2352175.
    $contexts[] = 'user';

    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();

    $bundle = $this->nodeTypeStorage->load($this->options['bundle']);
    $dependencies[$bundle->getConfigDependencyKey()][] = $bundle->getConfigDependencyName();

    foreach ($this->nodeStorage->loadMultiple($this->options['value']) as $term) {
      $dependencies[$term->getConfigDependencyKey()][] = $term->getConfigDependencyName();
    }

    return $dependencies;
  }

}
