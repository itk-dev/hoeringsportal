<?php

namespace Drupal\hoeringsportal_data\Helper;

use Drupal\Component\Utility\UrlHelper;
use Drupal\hoeringsportal_data\Exception\MapConfigurationParseException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Map helper.
 */
class MapHelper {

  /**
   * Parse configuration.
   *
   * @param string $configuration
   *   The configuration.
   * @param bool $silent
   *   Whether or not to throw parse exceptions.
   *
   * @return array|string
   *   The parsed configuration or the absolute URL to the configuration.
   */
  public function parseConfiguration($configuration, $silent = FALSE) {
    if (UrlHelper::isValid(trim($configuration), TRUE)) {
      return trim($configuration);
    }

    // Parse content as array.
    $value = NULL;

    if (preg_match('@^[a-z/.-]+$@', $configuration)) {
      $filename = $configuration;

      /** @var \Drupal\Core\File\FileSystem $fileSystem */
      $fileSystem = \Drupal::service('file_system');
      $path = $fileSystem->realpath(\Drupal::config('system.file')->get('default_scheme') . '://' . $filename);

      if (!file_exists($path)) {
        throw new MapConfigurationParseException('File ' . $filename . ' not found');
      }
      $configuration = file_get_contents($path);
    }

    // JSON.
    if (0 === strpos($configuration, '{')) {
      $value = \json_decode($configuration, TRUE);
      if (!$silent && NULL === $value) {
        throw new MapConfigurationParseException('Invalid JSON');
      }
    }
    else {
      // YAML.
      try {
        $value = Yaml::parse($configuration);
      }
      catch (ParseException $parseException) {
        if (!$silent) {
          throw new MapConfigurationParseException('Invalid YAML',
                                                   $parseException->getCode(), $parseException);
        }
      }
    }

    // Clean up configuration.
    if (isset($value['map']['layer']) && is_array($value['map']['layer'])) {
      foreach ($value['map']['layer'] as &$layer) {
        if (isset($layer['features_host'])) {
          $layer['features_host'] = $this->normalizeFeaturesHost($layer['features_host']);
        }
      }
    }

    return $value;
  }

  /**
   * Validate a map configuration.
   */
  public function validateConfiguration($configuration, $silent = FALSE) {
    return NULL !== $this->parseConfiguration($configuration, $silent);
  }

  /**
   * Normalize value of features_host.
   */
  private function normalizeFeaturesHost($value) {
    return preg_replace('/\s+/m', ' ', $value);
  }

}
