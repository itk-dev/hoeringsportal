<?php

namespace Drupal\hoeringsportal_deskpro\State;

use Drupal\Core\KeyValueStore\DatabaseStorage;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Serialization\Yaml;

/**
 * Formconfig for deskpro.
 */
class DeskproConfig extends DatabaseStorage {

  /**
   * Constructor.
   *
   * @param \Drupal\Component\Serialization\SerializationInterface $serializer
   *   The serializer.
   * @param \Drupal\Core\Database\Connection $connection
   *   The connection.
   */
  public function __construct(SerializationInterface $serializer, Connection $connection) {
    parent::__construct('hoeringsportal_deskpro.config', $serializer, $connection);
  }

  /**
   * Get available representations.
   */
  public function getRepresentations() {
    $representations = $this->get('representations', []);

    return array_filter($representations, function ($representation) {
      return $representation['is_available'];
    });
  }

  /**
   * Validate representations.
   */
  public function validateRepresentations($value) {
    Yaml::decode($value);
  }

  /**
   * Get Deskpro url.
   */
  public function getDeskproUrl() {
    return $this->get('deskpro_url');
  }

  /**
   * Get Deskpro api code key.
   */
  public function getDeskproApiCodeKey() {
    if (preg_match('/^([0-9]+):(.+)$/', $this->get('deskpro_api_code_key'), $matches)) {
      return [$matches[1], $matches[2]];
    }

    return [NULL, NULL];
  }

  /**
   * Ticket custom fields: name => title.
   *
   * @var array
   */
  private static $ticketCustomFields = [
    'hearing_id' => 'Hearing id',
    'hearing_name' => 'Hearing name',
    'edoc_id' => 'eDoc id',
    'pdf_download_url' => 'PDF download url',
    'representation' => 'Representation',
    'organization' => 'Organization (used with representation)',
    'address_secret' => 'Address is secret',
    'address' => 'Address',
    'postal_code' => 'Postal code',
    'geolocation' => 'Geo location',
    'message' => 'The actual hearing answer',
    'files' => 'Attached files',
    'accept_terms' => 'Accept terms',
    'unpublish_reply' => 'Unpublish reply',
  ];

  /**
   * Get ticket custom field mapping.
   */
  public function getTicketCustomFields() {
    $values = $this->get('deskpro_ticket_custom_fields', []);

    // Get default values: field name => NULL.
    $defaults = array_combine(
      array_keys(self::$ticketCustomFields),
      array_fill(0, count(self::$ticketCustomFields), NULL)
    );
    // Merge in actual values and remove invalid names.
    $fields = array_merge($defaults, $values);
    $fields = array_filter($fields, function ($name) use ($defaults) {
      return array_key_exists($name, $defaults);
    }, ARRAY_FILTER_USE_KEY);

    return $fields;
  }

  /**
   * Get ticket custom field titles.
   */
  public function getTicketCustomFieldTitles() {
    return self::$ticketCustomFields;
  }

  /**
   * Get ticket custom field id.
   */
  public function getTicketCustomFieldId($name) {
    $fields = $this->getTicketCustomFields();
    if (!isset($fields[$name])) {
      throw new \Exception('Invalid field: ' . $name);
    }

    return $fields[$name];
  }

  /**
   * Get available departments.
   */
  public function getAvailableDepartments() {
    return $this->get('deskpro_available_department_ids');
  }

  /**
   * Get data token.
   */
  public function getDataToken() {
    return $this->get('deskpro_data_token');
  }

  /**
   * Get cache ttl.
   */
  public function getCacheTtl() {
    return (int) $this->get('deskpro_cache_ttl');
  }

  /**
   * Get hearing id prefix (must be set in settings.php).
   */
  public function getHearingIdPrefix() {
    return \Drupal::config('hoeringsportal_deskpro.config')->get('deskpro_hearing_id_prefix');
  }

}
