<?php
/**
 * @file
 * Contains key/value storage for hoeringsportal_hotness settings.
 */

namespace Drupal\hoeringsportal_hotness\State;

use Drupal\Core\KeyValueStore\DatabaseStorage;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Database\Connection;

class HoeringsportalHotnessSettings extends DatabaseStorage {
  /**
   * @param \Drupal\Component\Serialization\SerializationInterface $serializer
   * @param \Drupal\Core\Database\Connection $connection
   */
  public function __construct(SerializationInterface $serializer, Connection $connection) {
    parent::__construct('hoeringsportal_hotness.settings', $serializer, $connection);
  }
}
