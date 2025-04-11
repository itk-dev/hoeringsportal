<?php

namespace Drupal\hoeringsportal_audit_log\Form;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\hoeringsportal_audit_log\Helpers\ConfigHelper;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\hoeringsportal_audit_log\Helpers\ConfigHelper $configHelper
   *   The configuration helper.
   */
  public function __construct(ConfigFactoryInterface $configFactory, protected RouteProviderInterface $routeProvider, protected EntityTypeManagerInterface $entityTypeManager, protected ConfigHelper $configHelper) {
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

    $enabledAuditIds = $this->configHelper->getEnabledAuditIds();

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
      '#type' => 'textarea',
      '#title' => $this->t('Route names'),
      '#default_value' => $config->get('logged_route_names') ? $this->fromArrayToString($config->get('logged_route_names')) : NULL,
      '#description' => $this->t('Route names (one per line) to log when users visit, they can look like this: <code>hoeringsportal_citizen_proposal.admin_supporter</code> or <code>node.add</code>, if in doubt, ask your friendly neighborhood programmer.'),
    ];

    /** @var \Drupal\Core\Entity\EntityTypeInterface[] $definitions */
    $definitions = $this->entityTypeManager->getDefinitions();

    $form['types'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Definition routes'),
      '#tree' => TRUE,
    ];

    foreach ($definitions as $definitionId => $definition) {
      if (in_array($definitionId, $enabledAuditIds)) {

        $bundleEntityType = $definition->getBundleEntityType();

        if ($bundleEntityType) {
          $storage = $this->entityTypeManager->getStorage($bundleEntityType);
          $types = $storage->loadMultiple();
        }
        else {
          $types = [$definition];
        }

        $linkTemplates = $definition->getLinkTemplates();

        if (count($types) > 0 && count($linkTemplates) > 0) {
          $form['types'][$definitionId] = [
            '#type' => 'fieldset',
            '#title' => $definition->getLabel(),
            '#tree' => TRUE,
            '#collapsible' => TRUE,
          ];

          foreach ($types as $type) {
            $typeId = $type->id();

            $form['types'][$definitionId][$typeId] = [
              '#type' => 'fieldset',
              '#title' => $type instanceof EntityTypeInterface ? $type->getLabel() : $type->label(),
              '#tree' => TRUE,
            ];

            $options = [];
            foreach ($linkTemplates as $path) {
              $matches = $this->routeProvider->getRoutesByPattern($path)->all();
              if (count($matches) > 0) {
                $routeKey = array_key_first($matches);
                $routeValue = reset($matches)->getPath();
                $options[$this->configHelper->escapeProviderId($routeKey)] = $routeValue;
              }
            }

            $form['types'][$definitionId][$typeId][] = [
              '#type' => 'checkboxes',
              '#options' => $options,
              '#default_value' => $this->getDefaultValues($definitionId, $typeId),
            ];

          }
        }
      }
    }
    return $form;
  }

  /**
   * Get default values for checkboxes.
   * 
   * @param string $definitionId
   * @param string $typeId
   * @return array<string, string>
   */
  private function getDefaultValues(string $definitionId, string $typeId) : array {
    $configTypes = $this->configHelper->getConfiguration('types');
    $defaultValues = [];
    if (count($configTypes) > 0) {
      $defaultValues = reset($configTypes[$definitionId][$typeId]);
    }
    return $defaultValues;
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-param array<mixed, mixed> $form
   */
  public function submitForm(array &$form, FormStateInterface $formState): void {
    $loggedRouteNames = $formState->getValue('logged_pages')['logged_route_names'];
    $this->configHelper->setConfiguration('logged_route_names', $this->fromStringToArray($loggedRouteNames));
   $types = $formState->getValue('types');
   $this->configHelper->setConfiguration('types', $types);

    $this->configHelper->saveConfig();
    parent::submitForm($form, $formState);
  }

  /**
   * Split string by newline and trim each item.
   * 
   * @param string $input
   * @return array<int, string>
   */
  private function fromStringToArray(string $input): array {
    return array_filter(array_map('trim', explode(PHP_EOL, $input)));
  }

  /**
   * Makes the array into a newline separated string.
   * 
   * @param array<string, string> $input
   * @return string
   */
  private function fromArrayToString(array $input): string {
    return implode(PHP_EOL, $input);
  }

}
