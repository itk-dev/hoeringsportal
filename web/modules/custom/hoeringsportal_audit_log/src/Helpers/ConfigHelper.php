<?php

namespace Drupal\hoeringsportal_audit_log\Helpers;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\hoeringsportal_audit_log\Form\SettingsForm;

/**
 * Config helper.
 */
class ConfigHelper {
  // Limit where audits can be made. If this is expanded, you probably need to write some code
  // in both SettingsForm and ControllerListener.
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
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->moduleConfig = $configFactory->getEditable(SettingsForm::SETTINGS);
  }

  /**
   * Returns enabled audits.
   *
   * @return array<int, string> Array of enabled audits, defined above.
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
   * @param array<int, string> $config
   *   Config to set.
   */
  public function setConfiguration(string $configName, array $config): void {
    $this->moduleConfig->set($configName, $config);
  }

  /**
   * Get configuration.
   *
   * @return array<string, array<string, array<int, array<string, string>>>> Configuration or null.
   */
  public function getConfiguration(string $configName): array|string|null {
    return $this->moduleConfig->get($configName) ?? NULL;
  }

  /**
   * Escape provider id.
   *
   * @return string String with __dot__ instead of .
   */
  public function escapeProviderId(string $input) {
    // Drupal will not accept a . in configuration keys. https://www.drupal.org/node/2297311
    return str_replace('.', '__dot__', $input);
  }

  /**
   * Get route names.
   *
   * @return array<string, string> Array of route names.
   */
  public function getRouteNames() {
    return $this->moduleConfig->get('logged_route_names');
  }

  /**
   * Get active (not 0) config for entity.
   */
  public function getEntityConfiguration(string $key, string $routeName, ?string $type): bool {
    // Retrieve the available types configuration.
    $types = $this->getConfiguration('types');

    // If no types configuration exists, return an empty array immediately.
    if (!$types) {
      return FALSE;
    }

    $typeId = $key;
    if ($type) {
      $typeId = $type;
    }

    $escapedRouteName = $this->escapeProviderId($routeName);
    return reset($types[$key][$typeId])[$escapedRouteName] == $escapedRouteName;
  }

}
