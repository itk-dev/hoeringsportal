<?php

namespace Drupal\hoeringsportal_audit_log\Helpers;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\hoeringsportal_audit_log\Form\SettingsForm;

/**
 * Config helper.
 */
class ConfigHelper {
  // Limit where audits can be made. If this is expanded, you probably need to write some code in both SettingsForm and
  // ControllerListener.
  private const ENABLED_AUDIT_IDS = ['node', 'user'];

  /**
   * The module config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $moduleConfig;

  /**
   * Confighelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory The config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->moduleConfig = $configFactory->getEditable(SettingsForm::SETTINGS);
  }

  /**
   * Returns enabled audits.
   */
  public static function getEnabledAuditIds() {

    return self::ENABLED_AUDIT_IDS;

  }

  /**
   * Save.
   */
  public function saveConfig() {
    $this->moduleConfig->save();
  }

  /**
   * Set configuration.
   */
  public function setConfiguration(string $configName, array $config): void {
    $this->moduleConfig->set($configName, $config);
  }

  /**
   * Get configuration.
   */
  public function getConfiguration(string $configName): array|string {
    return $this->moduleConfig->get($configName) ?? [];
  }

  /**
   * Escape provider id.
   */
  public function escapeProviderId(string $input) {
    // Drupal will not accept a . in configuration keys. https://www.drupal.org/node/2297311
    return str_replace('.', '__dot__', $input);
  }

  /**
   * Unescape provider id.
   */
  private function unescapeProviderId(string $input) {
    // Drupal will not accept a . in configuration keys. https://www.drupal.org/node/2297311
    return str_replace('__dot__', '.', $input);
  }

  /**
   * Get active (not 0) config for entity.
   */
  public function getEntityConfiguration(string $key, string $routeName, ?string $type): bool {
    $returnConfig = [];
    // Retrieve the available types configuration.
    $types = $this->getConfiguration('types');

    // If no types configuration exists, return an empty array immediately.
    if (!$types) {
      return $returnConfig;
    }

    $typeId = $key;
    if ($type) {
      $typeId = $type;
    }

    $escapedRouteName = $this->escapeProviderId($routeName);
    return reset($types[$key][$typeId])[$escapedRouteName] === $escapedRouteName;
  }

}
