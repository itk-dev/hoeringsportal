<?php

namespace Drupal\hoeringsportal_timeline\State;

use Drupal\Core\KeyValueStore\DatabaseStorage;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Database\Connection;

/**
 * Doc comment is empty.
 */
class HoeringsportalTimelineSettings extends DatabaseStorage {

  /**
   * Constructor.
   *
   * @param \Drupal\Component\Serialization\SerializationInterface $serializer
   *   The serializer.
   * @param \Drupal\Core\Database\Connection $connection
   *   The connection.
   */
  public function __construct(SerializationInterface $serializer, Connection $connection) {
    parent::__construct('hoeringsportal_timeline.settings', $serializer, $connection);
  }

}
