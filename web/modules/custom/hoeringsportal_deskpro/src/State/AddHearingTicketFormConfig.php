<?php

namespace Drupal\hoeringsportal_deskpro\State;

use Drupal\Core\KeyValueStore\DatabaseStorage;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Serialization\Yaml;

/**
 * Formconfig for deskpro.
 */
class AddHearingTicketFormConfig extends DatabaseStorage {

  /**
   * Constructor.
   *
   * @param \Drupal\Component\Serialization\SerializationInterface $serializer
   *   The serializer.
   * @param \Drupal\Core\Database\Connection $connection
   *   The connection.
   */
  public function __construct(SerializationInterface $serializer, Connection $connection) {
    parent::__construct('hoeringsportal_deskpro.add_hearing_ticket_form_config', $serializer, $connection);
  }

  /**
   * Get representations.
   */
  public function getRepresentations() {
    try {
      $value = $this->get('representations');
      $items = Yaml::decode($value);

      foreach ($items as $id => &$value) {
        if (!is_array($value)) {
          $value = [
            'label' => $value,
          ];
        }
      }

      return $items;
    }
    catch (\Exception $exception) {
      return [];
    }
  }

  /**
   * Get representations that require organization.
   */
  public function getRepresentationsThatRequireOrganization() {
    $representations = $this->getRepresentations();

    return array_filter($representations, function ($item) {
      return isset($item['require_organization']);
    });
  }

  /**
   * Validate representations.
   */
  public function validateRepresentations($value) {
    Yaml::decode($value);
  }

}
