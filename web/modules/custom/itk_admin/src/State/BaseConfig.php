<?php

namespace Drupal\itk_admin\State;

use Drupal\Core\KeyValueStore\DatabaseStorage;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Database\Connection;

/**
 * TODO: Description of what the class does.
 *
 * @package Drupal\itk_admin\State
 */
class BaseConfig extends DatabaseStorage {

  /**
   * The base config for itk_admin.
   *
   * @param \Drupal\Component\Serialization\SerializationInterface $serializer
   *   Defines an interface for serialization formats.
   * @param \Drupal\Core\Database\Connection $connection
   *   The PDO database abstraction class.
   */
  public function __construct(SerializationInterface $serializer, Connection $connection) {
    parent::__construct('itk_admin.itk_config', $serializer, $connection);
  }

}
