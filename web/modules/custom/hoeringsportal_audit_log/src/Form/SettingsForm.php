<?php

namespace Drupal\hoeringsportal_audit_log\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\NodeType;

/**
 * Settings form.
 */
final class SettingsForm extends ConfigFormBase {
  public const SETTINGS = 'hoeringsportal_audit_log.settings';

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory The config factory.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    parent::__construct($configFactory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hoeringsportal_audit_log_config';
  }

    /**
   * {@inheritdoc}
   *
   * @phpstan-return array<mixed, mixed>
   */
  protected function getEditableConfigNames(): array {
    return [
      self::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param array<mixed, mixed> $form
   * @phpstan-return array<mixed, mixed>
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config(self::SETTINGS);

    $form['logged_pages'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Logged pages'),
      '#tree' => TRUE,
    ];

    $form['logged_pages']['logged_route_names'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Route names'),
      '#default_value' => $config->get('logged_route_names') ? $this->fromArrayToString($config->get('logged_route_names')) : NULL,
      '#description' => $this->t("Comma seperated list. Route names to log when users visit, they can look like this: <code>hoeringsportal_citizen_proposal.admin_supporter</code>, if in doubt, ask your friendly neighborhood programmer."),
    ];

    $form['logged_content_types'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Logged content types'),
      '#tree' => TRUE,
    ];
 
    // Add config for all configured node types
    $nodeTypes = NodeType::loadMultiple();

    foreach ($nodeTypes as $nodeType) {
      $form['logged_content_types'][$nodeType->id()] = [
        '#type' => 'fieldset',
        '#title' => $nodeType->label(),
        '#tree' => TRUE,
      ];
      $defaultValues = $config->get('logged_content_types');

      // Make it possible to log when a user is on the view page of this content type
      $form['logged_content_types'][$nodeType->id()]['view'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Log view'),
        '#description' => $this->t('Log when a user views this content page (<code>/node/{node_id}</code>'),
        '#default_value' => $defaultValues[$nodeType->id()]['view'] ?? NULL,
      ];

      // Make it possible to log when a user is on the edit page of this content type
      $form['logged_content_types'][$nodeType->id()]['edit'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Log edit'),
        '#description' => $this->t('Log when a user views the edit content page (<code>/node/{node_id}/edit</code>'),
        '#default_value' => $defaultValues[$nodeType->id()]['edit'] ?? NULL,
      ];

      // Make it possible to log when a user is on the create page of this content type
      $form['logged_content_types'][$nodeType->id()]['create'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Log create'),
        '#description' => $this->t('Log when a user views the create content page (<code>/node/add/{content_type}</code>'),
        '#default_value' => $defaultValues[$nodeType->id()]['create'] ?? NULL,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param array<mixed, mixed> $form
   */
  public function submitForm(array &$form, FormStateInterface $formState): void {
    $config = $this->config(self::SETTINGS);
    $loggedRouteNames = $formState->getValue('logged_pages')['logged_route_names'];
    $config->set('logged_route_names', $loggedRouteNames ? $this->fromStringToArray($loggedRouteNames) : NULL );
    $config->set('logged_content_types', $formState->getValue('logged_content_types'));
    $config->save();
    parent::submitForm($form, $formState);
  }

  /**
   * Makes the input comma-separated string into an array.
   */
  private function fromStringToArray(string $input): array {
    return array_map('trim',explode(",",$input));
  }

  /**
   * Makes the array into a comma-separated string.
   */
  private function fromArrayToString(array $input): string {
    return implode(", ", $input);
  }
}
