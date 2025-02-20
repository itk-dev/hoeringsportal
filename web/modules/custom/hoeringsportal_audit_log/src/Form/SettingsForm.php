<?php

namespace Drupal\hoeringsportal_audit_log\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\node\Entity\NodeType;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;

/**
 * Settings form.
 */
final class SettingsForm extends ConfigFormBase {
  use AutowireTrait;

  public const SETTINGS = 'hoeringsportal_audit_log.settings';

  /**
   * Constructs a new form object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   A config factory for retrieving required config objects.
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   *   The route provider service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, protected RouteProviderInterface $routeProvider) {
    parent::__construct($configFactory);
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

    $url = Url::fromRoute('os2web_audit.plugin_settings_local_tasks');
    $form['info'] = [
      '#markup' => $this->t('This configuration handles <em>when</em> to create logs, <a href=":os2web_audit_settings_url">edit the <code>os2web_audit</code> configuration</a>.', [
        ':os2web_audit_settings_url' => Url::fromRoute('os2web_audit.plugin_settings_local_tasks')->toString(TRUE)->getGeneratedUrl(),
      ]),
    ];

    $form['logged_pages'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Logged pages'),
      '#tree' => TRUE,
    ];

    $form['logged_pages']['logged_route_names'] = [
      // @todo get all routes (a lot), find a way to filter them and display them in a multiselect.
      '#type' => 'textarea',
      '#title' => $this->t('Route names'),
      '#default_value' => $config->get('logged_route_names') ? $this->fromArrayToString($config->get('logged_route_names')) : NULL,
      '#description' => $this->t('Route names (one per line) to log when users visit, they can look like this: <code>hoeringsportal_citizen_proposal.admin_supporter</code>, if in doubt, ask your friendly neighborhood programmer.'),
    ];

    $form['logged_content_types'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Logged content types'),
      '#tree' => TRUE,
    ];

    // Add config for all configured node types.
    $nodeTypes = NodeType::loadMultiple();

    foreach ($nodeTypes as $nodeType) {
      $form['logged_content_types'][$nodeType->id()] = [
        '#type' => 'fieldset',
        '#title' => $nodeType->label(),
        '#tree' => TRUE,
      ];

      $defaultValues = $config->get('logged_content_types');

      // Make it possible to log when a user is on the view page of
      // this content type.
      $form['logged_content_types'][$nodeType->id()]['view'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Log view'),
        '#description' => $this->t('Log when a user views this content page (<code>/node/{node_id}</code>)'),
        '#default_value' => $defaultValues[$nodeType->id()]['view'] ?? NULL,
      ];

      // Make it possible to log when a user is on the edit page of
      // this content type.
      $form['logged_content_types'][$nodeType->id()]['edit'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Log edit'),
        '#description' => $this->t('Log when a user views the edit content page (<code>/node/{node_id}/edit</code>)'),
        '#default_value' => $defaultValues[$nodeType->id()]['edit'] ?? NULL,
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
    $config->set('logged_route_names', $loggedRouteNames ? $this->fromStringToArray($loggedRouteNames) : NULL);
    $config->set('logged_content_types', $formState->getValue('logged_content_types'));
    $config->save();
    parent::submitForm($form, $formState);
  }

  /**
   * Split string by newline and trim each item.
   */
  private function fromStringToArray(string $input): array {
    return array_filter(array_map('trim', explode(PHP_EOL, $input)));
  }

  /**
   * Makes the array into a newline separated string.
   */
  private function fromArrayToString(array $input): string {
    return array_filter(array_map('trim', implode(PHP_EOL, $input)));
  }

}
