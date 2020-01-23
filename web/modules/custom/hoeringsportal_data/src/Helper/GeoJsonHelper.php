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
   * Get projects.
   */
  public function getProjects(array $properties = []) {
    $properties += [
      'status' => NodeInterface::PUBLISHED,
      'type' => 'project',
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
        'teaser' => $entity->get('field_teaser')->value,
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
   * Serialize Project as GeoJSON.
   */
  public function serializeGeoJsonProject(NodeInterface $project) {
    $areas = $project->get('field_area')->referencedEntities();
    $tags = $project->get('field_tags')->referencedEntities();

    $lokalplaner = [];
    foreach ($project->get('field_lokalplaner') as $lokalplan) {
      $lokalplaner[] = $lokalplan;
    }

    $geometryType = $this->getGeometryType($project);

    $data = [
      'properties' => [
        'project_id' => (int) $project->id(),
        'project_title' => $project->getTitle(),
        'project_teaser' => $project->get('field_teaser')->value,
        'project_description' => $project->get('field_description')->value,
        'project_start' => $this->getDateTime($project->get('field_project_start')->value),
        'project_finish' => $this->getDateTime($project->get('field_project_finish')->value),
        'project_tags' => array_map([$this, 'getTermName'], $tags),
        'project_teaser' => $project->get('field_teaser')->value,
        'project_contact' => $project->get('field_contact')->value,
        'project_phone' => $project->get('field_phone')->value,
        'project_geometry_type' => $geometryType,
        'project_url' => $this->generateUrl('entity.node.canonical', ['node' => $project->id()]),
        'project_area_list' => $this->listify(array_map(function (Term $term) {
          return $term->get('field_area_id')->value;
        }, $areas)),
        'project_area_ids' => array_map(function (Term $term) {
          return (int) $term->get('field_area_id')->value;
        }, $areas),
        'project_local_plan_list' => $this->listify(array_map(function ($lokalplan) {
          return $lokalplan->id;
        }, $lokalplaner)),
        'project_local_plan_ids' => array_map(function ($lokalplan) {
          return (int) $lokalplan->id;
        }, $lokalplaner),
      ],
    ];

    $geometry = $this->getGeometry($project);
    if (NULL !== $geometry) {
      $data['geometry'] = $geometry['geometry'];
      $data['type'] = 'Feature';
    }

    return $data;
  }

  /**
   * Serialize Hearing as GeoJSON.
   */
  public function serializeGeoJsonHearing(NodeInterface $hearing) {
    $areas = $hearing->get('field_area')->referencedEntities();
    /** @var \Drupal\taxonomy\Entity\Term $hearing_type */
    $hearing_type = $this->getReference($hearing, 'field_hearing_type');
    $project = $this->getReference($hearing, 'field_project_reference');
    $data = \json_decode($hearing->get('field_deskpro_data')->value, TRUE);

    $tags = $hearing->get('field_tags')->referencedEntities();

    $lokalplaner = [];
    foreach ($hearing->get('field_lokalplaner') as $lokalplan) {
      $lokalplaner[] = $lokalplan;
    }

    $geometryType = $this->getGeometryType($hearing);

    $data = [
      'properties' => [
        'hearing_id' => (int) $hearing->id(),
        'hearing_title' => $hearing->getTitle(),
        'hearing_content_state' => $hearing->get('field_content_state')->value,
        'hearing_description' => $hearing->get('field_description')->value,
        'hearing_type' => $this->getTermName($hearing_type),
        'hearing_reply_deadline' => $this->getDateTime($hearing->get('field_reply_deadline')->value),
        'hearing_start_date' => $this->getDateTime($hearing->get('field_start_date')->value),
        'hearing_tags' => array_map([$this, 'getTermName'], $tags),
        'hearing_teaser' => $hearing->get('field_teaser')->value,
        'hearing_geometry_type' => $geometryType,
        'hearing_replies_count' => isset($data['tickets']) ? \count($data['tickets']) : 0,
        'hearing_replies_url' => $this->generateUrl('entity.node.canonical', ['node' => $hearing->id()], ['fragment' => 'hearing-tickets']),
        'hearing_url' => $this->generateUrl('entity.node.canonical', ['node' => $hearing->id()]),
        'hearing_project_id' => $project ? (int) $project->id() : NULL,
        'hearing_project_title' => $project ? $project->get('title')->value : NULL,
        'hearing_project_url' => $project ? $this->generateUrl('entity.node.canonical', ['node' => $project->id()]) : NULL,
        'hearing_area_list' => $this->listify(array_map(function (Term $term) {
          return $term->get('field_area_id')->value;
        }, $areas)),
        'hearing_area_ids' => array_map(function (Term $term) {
          return (int) $term->get('field_area_id')->value;
        }, $areas),
        'hearing_local_plan_list' => $this->listify(array_map(function ($lokalplan) {
          return $lokalplan->id;
        }, $lokalplaner)),
        'hearing_local_plan_ids' => array_map(function ($lokalplan) {
          return (int) $lokalplan->id;
        }, $lokalplaner),
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
    $properties = [
      'ticket_id' => $data->id,
      'ticket_hearing_id' => (int) $ticket->hearing->id(),
      'ticket_message' => $fields->message ?? NULL,
      'ticket_person_name' => $data->person->name ?? NULL,
      'ticket_organization' => $fields->organization ?? NULL,
      'ticket_pdf_download_url' => $fields->pdf_download_url ?? NULL,
      'ticket_url' => $this->generateUrl('hoeringsportal_deskpro.hearing.ticket_view', [
        'node' => $ticket->hearing->id(),
        'ticket' => $data->id,
      ]),
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
   * @param string $mapFieldName
   *   The map field name.
   */
  private function getGeometryType(NodeInterface $entity, string $mapFieldName = 'field_map') {
    if (!$entity->hasField($mapFieldName)) {
      return NULL;
    }

    $value = $entity->get($mapFieldName)->getValue();

    if (empty($value) || !isset($value[0]['type'])) {
      return NULL;
    }

    switch ($value[0]['type']) {
      case MapItem::TYPE_POINT:
        return self::GEOMETRY_POINT;

      case MapItem::TYPE_LOCALPLANIDS:
      case MapItem::TYPE_LOCALPLANIDS_NODE:
        return self::GEOMETRY_LOCAL_PLAN;
    }

    return NULL;
  }

  /**
   * Empty geometry object.
   *
   * Empty (sort of) geometry to use as a fallback when no real geometry is
   * available.
   *
   * @var array
   */
  private static $emptyGeometry = [
    'geometry' => [
      'type' => 'Point',
      'coordinates' => [0, 0],
    ],
  ];

  /**
   * Get geometry.
   */
  private function getGeometry(NodeInterface $entity) {
    $value = $entity->get('field_map')->getValue();

    if (empty($value) || !isset($value[0]['data'])) {
      return self::$emptyGeometry;
    }

    // For now we're only interested in points.
    // Other map data will be joined into hearing data.
    if (MapItem::TYPE_POINT !== $value[0]['type']) {
      return self::$emptyGeometry;
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

    if (NULL === $reference) {
      return NULL;
    }

    $entity = $reference->get('entity');
    if (NULL === $entity) {
      return NULL;
    }

    $target = $entity->getTarget();

    return NULL !== $target ? $target->getValue() : NULL;
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
