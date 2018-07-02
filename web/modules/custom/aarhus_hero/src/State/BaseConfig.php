<?php

namespace Drupal\aarhus_hero\State;

use Drupal\Core\KeyValueStore\DatabaseStorage;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Database\Connection;

/**
 * Doc comment is empty.
 */
class BaseConfig extends DatabaseStorage {

  /**
   * Constructor.
   *
   * @param \Drupal\Component\Serialization\SerializationInterface $serializer
   *   The serializer.
   * @param \Drupal\Core\Database\Connection $connection
   *   The connection.
   */
  public function __construct(SerializationInterface $serializer, Connection $connection) {
    parent::__construct('aarhus_hero.hero_config', $serializer, $connection);
  }

}
