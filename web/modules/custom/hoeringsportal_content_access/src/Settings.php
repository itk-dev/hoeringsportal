<?php

namespace Drupal\hoeringsportal_content_access;

use Drupal\Core\Site\Settings as CoreSettings;

/**
 * Settings helper for the module.
 */
final class Settings {

  /**
   * Check if department access check must be bypassed.
   */
  public function bypassDepartmentAccessCheck(): bool {
    return (bool) (static::getAll()['bypass_department_access_check'] ?? FALSE);
  }

  /**
   * Get department vocabulary ID.
   */
  public function getDepartmentVocabularyId(): string {
    return 'department';
  }

  /**
   * Get user department claim name.
   */
  public function getUserDepartmentClaim(): string {
    return (string) (static::getAll()['user_department_claim'] ?? 'magistratsafdeling');
  }

  /**
   * Get all settings.
   *
   * @return array
   *   All the settings.
   */
  private function getAll(): array {
    $settings = CoreSettings::get('hoeringsportal_content_access');

    return is_array($settings) ? $settings : [];
  }

}
