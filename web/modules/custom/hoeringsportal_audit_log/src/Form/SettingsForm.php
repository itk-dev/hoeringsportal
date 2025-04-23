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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory A config factory for retrieving required config
   *   objects.
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider The route provider service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager The entity type manager.
   * @param \Drupal\hoeringsportal_audit_log\Helpers\ConfigHelper $configHelper The configuration helper.
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

    // EntityType (implements EntityTypeInterface) contains meta data for entity types, e.g. node, taxonomy term. Below
    // getDefinitions, return the current definitions in the Drupal installation, and their meta data.

    /** @var \Drupal\Core\Entity\EntityTypeInterface[] $definitions */
    $definitions = $this->entityTypeManager->getDefinitions();

    $form['types'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Definition routes'),
      '#tree' => TRUE,
    ];

    // In the helper, I have created a limit for which entity types that can be audited.
    $enabledAuditIds = $this->configHelper->getEnabledAuditIds();
    foreach ($definitions as $definitionId => $definition) {
      if (in_array($definitionId, $enabledAuditIds)) {

        // If the entity type has a bundle (subtype/different types of content) E.g. for node it is "node_type", and for
        // user NULL.
        $bundleEntityType = $definition->getBundleEntityType();
        if ($bundleEntityType) {
          $storage = $this->entityTypeManager->getStorage($bundleEntityType);
          // If the entity type has a bundle, we use that as the types An example of a node_type in this project is a
          // citizen_proposal.
          $types = $storage->loadMultiple();
        }
        else {
          // If the bundle is NULL, meaning there are no bundles, we have reached "the bottom" of the tree and therefore
          // we use the definition as type.
          $types = [$definition];
        }

        // The routes for the entity type e.g. 'canonical' => '/node/{node}'.
        $linkTemplates = $definition->getLinkTemplates();

        if (count($types) > 0 && count($linkTemplates) > 0) {

          // The definition is the wrapper (user, node)
          $form['types'][$definitionId] = [
            '#type' => 'fieldset',
            '#title' => $definition->getLabel(),
            '#tree' => TRUE,
            '#collapsible' => TRUE,
          ];

          // Below loops through the subtypes/bundles of the definition, and in the case where there are no bundles, it
          // uses the definition.
          foreach ($types as $type) {
            $typeId = $type->id();

            // For the entity type with bundles (e.g. node), the definitionId will have multiple entries (e.g.
            // citizen_proposal, static_page) For the entity type without bundles, there will be one entry with a sub
            // entry called the same E.g. user with the sub entry user (as both $definitionId and $typeId is user
            // because they are the id of the same entity type)
            $form['types'][$definitionId][$typeId] = [
              '#type' => 'fieldset',
              '#title' => $type instanceof EntityTypeInterface ? $type->getLabel() : $type->label(),
              '#tree' => TRUE,
            ];

            $options = [];

            // Here, the routes for the entity types are created as checkboxes, so the user can check the routes that
            // are supposed to be audit logged.
            foreach ($linkTemplates as $path) {
              $matches = $this->routeProvider->getRoutesByPattern($path)->all();
              if (count($matches) > 0) {
                // RouteKey is the name we are interested in, e.g. canonical.
                $routeKey = array_key_first($matches);
                // RouteKey is the more human understandable name, e.g. '/node/{node}'.
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
   * @param string $definitionId definitionId.
   * @param string $typeId typeId.
   *
   * @return array<string, string> Default values from config.
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
   * @param string $input Input.
   *
   * @return array<int, string> Array from string, entries separated by end of line.
   */
  private function fromStringToArray(string $input): array {
    return array_filter(array_map('trim', explode(PHP_EOL, $input)));
  }

  /**
   * Makes the array into a newline separated string.
   *
   * @param array<string, string> $input Input.
   *
   * @return string String from array.
   */
  private function fromArrayToString(array $input): string {
    return implode(PHP_EOL, $input);
  }

}
