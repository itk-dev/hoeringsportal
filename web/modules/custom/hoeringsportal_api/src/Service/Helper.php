<?php

namespace Drupal\hoeringsportal_api\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Helper.
 */
class Helper {
  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  private $urlGenerator;

  /**
   * GeoJSON helper.
   *
   * @var \Drupal\hoeringsportal_api\Service\GeoJsonHelper
   */
  private $geoJsonHelper;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, UrlGeneratorInterface $urlGenerator, GeoJsonHelper $geoJSONHelper) {
    $this->entityTypeManager = $entityTypeManager;
    $this->urlGenerator = $urlGenerator;
    $this->geoJsonHelper = $geoJSONHelper;
  }

  /**
   * Get hearings.
   */
  public function getHearings(array $properties = []) {
    $properties += [
      'status' => NodeInterface::PUBLISHED,
      'type' => 'hearing',
    ];
    $entities = $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties($properties);

    return $entities;
  }

  /**
   * Serialize a Hearing.
   */
  public function serializeHearing(Node $entity) {
    $areas = $entity->get('field_area')->referencedEntities();
    /** @var \Drupal\taxonomy\Entity\Term $hearing_type */
    $hearing_type = $this->getReference($entity, 'field_hearing_type');
    $project_reference = $this->getReference($entity, 'field_project_reference');
    $tags = $entity->get('field_tags')->referencedEntities();

    $lokalplaner = [];
    foreach ($entity->get('field_lokalplaner') as $lokalplan) {
      $lokalplaner[] = $lokalplan->id;
    }

    $geojson = \json_decode($entity->get('field_map_display')->value, TRUE);

    return [
      'properties' => [
        'id' => $entity->id(),
        'title' => $entity->getTitle(),
        'areas' => array_map([$this, 'getTermName'], $areas),
        'content_state' => $entity->get('field_content_state')->value,
        'description' => $entity->get('field_description')->value,
        'hearing_type' => $this->getTermName($hearing_type),
        'project_reference' => $project_reference ? $project_reference->getTitle() : NULL,
        'reply_deadline' => $this->getDateTime($entity->get('field_reply_deadline')->value),
        'start_date' => $this->getDateTime($entity->get('field_start_date')->value),
        'tags' => array_map([$this, 'getTermName'], $tags),
        'teaser' => $entity->get('field_teaser')->value,
        'lokalplaner' => $lokalplaner,
        'geojson' => $geojson,
      ],
      'links' => [
        'self' => $this->generateUrl('hoeringsportal_api.api_controller_hearings_show', ['hearing' => $entity->id()]),
      ],
    ];
  }

  /**
   * Serialize as GeoJSON.
   */
  public function serializeGeoJson($entity) {
    if ($entity instanceof NodeInterface) {
      return $this->serializeGeoJsonHearing($entity);
    }
    elseif (is_object($entity)) {
      return $this->serializeGeoJsonTicket($entity);
    }
    else {
      throw new \RuntimeException('Cannot serialize as GeoJSON');
    }
  }

  /**
   * Serialize Hearing as GeoJSON.
   */
  private function serializeGeoJsonHearing(NodeInterface $hearing) {
    $areas = $hearing->get('field_area')->referencedEntities();
    /** @var \Drupal\taxonomy\Entity\Term $hearing_type */
    $hearing_type = $this->getReference($hearing, 'field_hearing_type');
    $project_reference = $this->getReference($hearing, 'field_project_reference');
    $tags = $hearing->get('field_tags')->referencedEntities();

    $lokalplaner = [];
    foreach ($hearing->get('field_lokalplaner') as $lokalplan) {
      $lokalplaner[] = $lokalplan->id;
    }

    $geometry = $this->getGeometry($hearing);

    return [
      'properties' => [
        'id' => $hearing->id(),
        'title' => $hearing->getTitle(),
        'areas' => array_map([$this, 'getTermName'], $areas),
        'content_state' => $hearing->get('field_content_state')->value,
        'description' => $hearing->get('field_description')->value,
        'hearing_type' => $this->getTermName($hearing_type),
        'project_reference' => $project_reference ? $project_reference->getTitle() : NULL,
        'reply_deadline' => $this->getDateTime($hearing->get('field_reply_deadline')->value),
        'start_date' => $this->getDateTime($hearing->get('field_start_date')->value),
        'tags' => array_map([$this, 'getTermName'], $tags),
        'teaser' => $hearing->get('field_teaser')->value,
        'lokalplaner' => $lokalplaner,
      ],
      'geometry' => $geometry,
      'type' => 'Feature',
    ];
  }

  /**
   * Serialize Ticket as GeoJSON.
   */
  private function serializeGeoJsonTicket(object $ticket) {
    $serialized = $this->serializeGeoJsonHearing($ticket->hearing);

    $properties = &$serialized['properties'];
    $keys = array_keys($properties);
    foreach ($keys as $key) {
      $properties['hearing_' . $key] = $properties[$key];
      unset($properties[$key]);
    }

    $data = $ticket->data;
    $fields = $data->fields;
    $properties += [
      'message' => $fields->message ?? NULL,
      'person_name' => $data->person->name ?? NULL,
      'organization' => $fields->organization ?? NULL,
      'pdf_download_url' => $fields->pdf_download_url ?? NULL,
    ];

    return $serialized;
  }

  /**
   * Get geometry.
   */
  private function getGeometry(NodeInterface $entity) {
    $value = $entity->get('field_map')->getValue();
    if (empty($value) || !isset($value[0]['data'])) {
      return NULL;
    }

    $geojson = \json_decode($value[0]['data'], TRUE);

    if (empty($geojson)) {
      return NULL;
    }

    return $this->geoJsonHelper->featureCollectionToMulti($geojson);
  }

  /**
   * Get entity reference from an entity.
   */
  private function getReference($entity, $field_name) {
    $reference = $entity->get($field_name)->first();

    return NULL !== $reference ? $reference->get('entity')->getTarget()->getValue() : NULL;
  }

  /**
   * Get term name.
   */
  private function getTermName(Term $term = NULL) {
    return $term ? $term->get('name')->value : NULL;
  }

  /**
   * Get formattted date time.
   */
  private function getDateTime($value) {
    return $value ? (new \DateTime($value))->format(\DateTime::ATOM) : NULL;
  }

  /**
   * Generate url.
   */
  public function generateUrl($name, $parameters = [], $options = []) {
    $options += [
      'absolute' => TRUE,
    ];

    return $this->urlGenerator->generateFromRoute($name, $parameters, $options);
  }

}
