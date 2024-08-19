<?php

namespace Drupal\hoeringsportal_citizen_proposal\Helper;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Site\Settings;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Citizen access checker.
 */
class CitizenAccessChecker {
  use LoggerAwareTrait;

  /**
   * Constructor.
   */
  public function __construct(
    LoggerChannel $logger,
  ) {
    $this->setLogger($logger);
  }

  /**
   * Check access for CPR result.
   *
   * One of the matches in cpr_result_checks must match to grant access.
   *
   * @param array $result
   *   The CPR result.
   *
   * @return bool
   *   True if access is granted.
   */
  public function checkAccess(array $result): bool {
    $accessGranted = FALSE;
    $settings = (array) (Settings::get('hoeringsportal_citizen_proposal')['access_check'] ?? []);
    $checks = (array) ($settings['cpr_access_checks'] ?? []);
    $propertyAccessor = PropertyAccess::createPropertyAccessor();
    // Keep track of checked values.
    $checkedValues = [];
    foreach ($checks as $path => $allowedValues) {
      $value = $propertyAccessor->isReadable($result, $path)
        ? $propertyAccessor->getValue($result, $path)
        : NULL;
      // We deliberately use non-strict comparison here.
      $isMatch = in_array($value, (array) $allowedValues);
      if ($isMatch) {
        $accessGranted = TRUE;
      }

      $checkedValues[$path] = [
        'value' => $value,
        'is_match' => $isMatch,
      ];
    }

    $this->logger->debug('Citizen access @granted: @values', [
      '@granted' => $accessGranted ? 'granted' : 'denied',
      '@values' => json_encode($checkedValues),
    ]);

    return $accessGranted;
  }

}
