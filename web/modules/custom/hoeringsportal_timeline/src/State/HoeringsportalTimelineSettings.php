<?php
/**
 * @file
 * Contains key/value storage for hoeringsportal_timeline settings.
 */

namespace Drupal\hoeringsportal_timeline\State;

use Drupal\Core\KeyValueStore\DatabaseStorage;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Database\Connection;

class HoeringsportalTimelineSettings extends DatabaseStorage {
  /**
   * @param \Drupal\Component\Serialization\SerializationInterface $serializer
   * @param \Drupal\Core\Database\Connection $connection
   */
  public function __construct(SerializationInterface $serializer, Connection $connection) {
    parent::__construct('hoeringsportal_timeline.settings', $serializer, $connection);
  }
}
