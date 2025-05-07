<?php

namespace Drupal\hoeringsportal_audit_log\Helpers;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\hoeringsportal_audit_log\Form\SettingsForm;

/**
 * Config helper.
 */
class ConfigHelper {
  // Limit where audits can be made. If this is expanded, you probably need to
  // write some code in both SettingsForm and ControllerListener.
  private const ENABLED_AUDIT_IDS = ['node', 'user'];

  /**
   * The module config.
   *
   * @var \Drupal\Core\Config\Config
   */
  private $moduleConfig;

  /**
   * Confighelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config
   *   factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->moduleConfig = $configFactory->getEditable(SettingsForm::SETTINGS);
  }

  /**
   * Returns enabled audits.
   *
   * @return array<int, string>
   *   Array of enabled audits, defined above.
   */
  public static function getEnabledAuditIds(): array {
    return self::ENABLED_AUDIT_IDS;

  }

  /**
   * Save.
   */
  public function saveConfig(): void {
    $this->moduleConfig->save();
  }

  /**
   * Set configuration.
   *
   * @param string $configName
   *   Config name.
   * @param array<int, string>|string $config
   *   Config to set.
   */
  public function setConfiguration(string $configName, array|string $config): void {
    $this->moduleConfig->set($configName, $config);
  }

  /**
   * Get configuration.
   *
   * @return array<string, array<string, array<int, array<string, string>>>>
   *   Configuration or null.
   */
  public function getConfiguration(string $configName): array|string|null {
    return $this->moduleConfig->get($configName) ?? NULL;
  }

  /**
   * Escape provider id.
   *
   * @param string $input
   *   The input string.
   *
   * @return string
   *   A string with __dot__ instead of .
   */
  public function escapeProviderId(string $input) {
    // Drupal will not accept a . in configuration keys.
    // https://www.drupal.org/node/2297311
    return str_replace('.', '__dot__', $input);
  }

  /**
   * Get routes to audit from config.
   *
   * @return array<string, string>
   *   routes to audit.
   */
  private function getRoutesToAudit() : array {
    try {
      $routesToAudit = $this->moduleConfig->get('routes_to_audit');

      if ($routesToAudit) {
        $routesToAudit = Yaml::decode($routesToAudit);
        return $routesToAudit;
      }

      return [];
    }
    catch (\Exception) {
      return [];
    }
  }

  /**
   * Get route names.
   *
   * @return array<int, string>|null
   *   Array of route names or NULL.
   */
  public function getRouteNames() : array {
    return (array) ($this->getRoutesToAudit()['routes'] ?? NULL);
  }

  
  /**
   * Get url patterns from config.
   *
   * @return array<int, string>
   *   Array of url patterns.
   */
  public function getUrlPatterns() : array {
    return (array) ($this->getRoutesToAudit()['url_pattern'] ?? NULL);
  }

  /**
   * Get active (not 0) config for entity.
   *
   * @param string $currentRouteName
   *   The current route.
   * @param string $entityTypeId
   *   The id of the entity type.
   * @param string|null $bundleType
   *   The bundle type, if any.
   *
   * @return bool
   *   If the config is active.
   */
  public function isConfigActive(string $currentRouteName, string $entityTypeId, ?string $bundleType): bool {
    // Retrieve the available types configuration.
    $types = $this->getConfiguration('types');

    // If no types configuration exists, return an empty array immediately.
    if (!$types) {
      return FALSE;
    }

    // Only some entity types has a bundletype, if they do, we need that to find
    // it in config if we dont, the configuration just has the entity type
    // twice. (e.g. user -> user)
    $typeId = $bundleType ?: $entityTypeId;

    $escapedRouteName = $this->escapeProviderId($currentRouteName);

    // See if, in the config, the route name has the route name as value,
    // instead of 0.
    return reset($types[$entityTypeId][$typeId])[$escapedRouteName] === $escapedRouteName;
  }

}
