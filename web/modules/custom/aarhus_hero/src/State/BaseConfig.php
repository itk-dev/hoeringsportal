<?php
/**
 * @file
 * Contains key/value storage for aarhus hero config .
 */

namespace Drupal\aarhus_hero\State;

use Drupal\Core\KeyValueStore\DatabaseStorage;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Database\Connection;

class BaseConfig extends DatabaseStorage {
  /**
   * @param \Drupal\Component\Serialization\SerializationInterface $serializer
   * @param \Drupal\Core\Database\Connection $connection
   */
  public function __construct(SerializationInterface $serializer, Connection $connection) {
    parent::__construct('aarhus_hero.hero_config', $serializer, $connection);
  }
}