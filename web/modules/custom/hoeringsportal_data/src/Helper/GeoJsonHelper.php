<?php

namespace Drupal\hoeringsportal_data\Helper;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\hoeringsportal_data\Plugin\Field\FieldType\MapItem;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Helper.
 */
class GeoJsonHelper {
  const GEOMETRY_POLYGON = 'polygon';
  const GEOMETRY_LOCAL_PLAN = 'local_plan';
  const GEOMETRY_POINT = 'point';

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
   * Hearing helper.
   *
   * @var \Drupal\hoeringsportal_data\Helper\HearingHelper
   */
  private $helper;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, UrlGeneratorInterface $urlGenerator, HearingHelper $helper) {
    $this->entityTypeManager = $entityTypeManager;
    $this->urlGenerator = $urlGenerator;
    $this->helper = $helper;
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
        'id' => (int) $entity->id(),
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
        'self' => $this->generateUrl('hoeringsportal_data.api_controller_hearings_show', ['hearing' => $entity->id()]),
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
  public function serializeGeoJsonHearing(NodeInterface $hearing) {
    $areas = $hearing->get('field_area')->referencedEntities();
    /** @var \Drupal\taxonomy\Entity\Term $hearing_type */
    $hearing_type = $this->getReference($hearing, 'field_hearing_type');
    $project_reference = $this->getReference($hearing, 'field_project_reference');
    $tags = $hearing->get('field_tags')->referencedEntities();

    $lokalplaner = [];
    foreach ($hearing->get('field_lokalplaner') as $lokalplan) {
      $lokalplaner[] = (int) $lokalplan->id;
    }

    $geometryType = $this->getGeometryType($hearing);

    $data = [
      'properties' => [
        'hearing_id' => (int) $hearing->id(),
        'hearing_title' => $hearing->getTitle(),
        'hearing_areas' => array_map([$this, 'getTermName'], $areas),
        'hearing_area_ids' => $this->listify(array_map(function (Term $term) {
          return $term->get('field_area_id')->value;
        }, $areas)),
        'hearing_content_state' => $hearing->get('field_content_state')->value,
        'hearing_description' => $hearing->get('field_description')->value,
        'hearing_type' => $this->getTermName($hearing_type),
        'hearing_project_reference' => $project_reference ? $project_reference->getTitle() : NULL,
        'hearing_reply_deadline' => $this->getDateTime($hearing->get('field_reply_deadline')->value),
        'hearing_start_date' => $this->getDateTime($hearing->get('field_start_date')->value),
        'hearing_tags' => array_map([$this, 'getTermName'], $tags),
        'hearing_teaser' => $hearing->get('field_teaser')->value,
        'hearing_localplan_ids' => $this->listify($lokalplaner),
        'hearing_geometry_type' => $geometryType,
      ],
    ];

    $geometry = $this->getGeometry($hearing);
    if (NULL !== $geometry) {
      $data['geometry'] = $geometry['geometry'];
      $data['type'] = 'Feature';
    }

    return $data;
  }

  /**
   * Serialize Ticket as GeoJSON.
   */
  public function serializeGeoJsonTicket(object $ticket) {
    $serialized = $this->serializeGeoJsonHearing($ticket->hearing);

    $properties = &$serialized['properties'];

    $data = $ticket->data;
    $fields = $data->fields;
    $properties += [
      'ticket_message' => $fields->message ?? NULL,
      'ticket_person_name' => $data->person->name ?? NULL,
      'ticket_organization' => $fields->organization ?? NULL,
      'ticket_pdf_download_url' => $fields->pdf_download_url ?? NULL,
    ];

    return $serialized;
  }

  /**
   * Convert a list into csv format.
   */
  private function listify(array $values) {
    return implode(',', array_filter($values));
  }

  /**
   * Get the type of geometry associated with an entity.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   */
  private function getGeometryType(NodeInterface $entity) {
    if (!$this->helper->isHearing($entity)) {
      return NULL;
    }

    $value = $entity->get('field_map')->getValue();

    if (empty($value) || !isset($value[0]['type'])) {
      return NULL;
    }

    switch ($value[0]['type']) {
      case MapItem::TYPE_ADDRESS:
        return self::GEOMETRY_POINT;

      case MapItem::TYPE_LOCALPLANIDS:
      case MapItem::TYPE_LOCALPLANIDS_NODE:
        return self::GEOMETRY_LOCAL_PLAN;
    }
  }

  /**
   * Get geometry.
   */
  private function getGeometry(NodeInterface $entity) {
    $value = $entity->get('field_map')->getValue();

    if (empty($value) || !isset($value[0]['data'])) {
      return NULL;
    }

    // For now we're only interested in points.
    // Other map data will be joined into hearing data.
    if (MapItem::TYPE_ADDRESS !== $value[0]['type']) {
      // Fake geometry object.
      return [
        'geometry' => [
          'type' => 'Point',
          'coordinates' => [0, 0],
        ],
      ];
    }

    $geojson = \json_decode($value[0]['data'], TRUE);

    if (empty($geojson)) {
      return NULL;
    }

    return $this->featureCollectionToMulti($geojson);
  }

  /**
   * Convert a FeatureCollection to a Multi-collection.
   */
  public function featureCollectionToMulti(array $geojson) {
    if (!isset($geojson['type']) || 'FeatureCollection' !== $geojson['type']) {
      return $geojson;
    }

    $type = NULL;
    $isMulti = FALSE;
    $coordinates = [];

    foreach ($geojson['features'] as $feature) {
      $geometry = $feature['geometry'];
      if (NULL === $type) {
        $type = $geometry['type'];
        $isMulti = preg_match('/^Multi/', $type);
      }
      elseif ($type !== $geometry['type']) {
        // Different type in feature.
        continue;
      }

      if ($isMulti) {
        $coordinates = \array_merge($coordinates, $geometry['coordinates']);
      }
      else {
        $coordinates[] = $geometry['coordinates'];
      }
    }

    return [
      'type' => $isMulti ? $type : 'Multi' . $type,
      'coordinates' => $coordinates,
    ];
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
