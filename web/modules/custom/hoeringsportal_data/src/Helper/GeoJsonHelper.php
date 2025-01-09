<?php

namespace Drupal\hoeringsportal_data\Helper;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\hoeringsportal_data\Plugin\Field\FieldType\MapItem;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper as DeskproHearingHelper;

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
   * The hearing helper.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\HearingHelper
   */
  private DeskproHearingHelper $deskproHelper;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, UrlGeneratorInterface $urlGenerator, HearingHelper $helper, DeskproHearingHelper $deskproHelper) {
    $this->entityTypeManager = $entityTypeManager;
    $this->urlGenerator = $urlGenerator;
    $this->helper = $helper;
    $this->deskproHelper = $deskproHelper;
  }

  /**
   * Get hearings.
   */
  public function getHearings(array $properties = [], array $orderBy = [], $limit = null, $offset = null) {
    return $this->loadEntities(
      'node',
      $properties + [
        'status' => NodeInterface::PUBLISHED,
        'type' => 'hearing',
      ],
      $orderBy,
      $limit,
      $offset
    );
  }

  /**
   * Get public meetings.
   */
  public function getPublicMeetings(array $properties = []) {
    $properties += [
      'status' => NodeInterface::PUBLISHED,
      'type' => 'public_meeting',
    ];
    return $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties($properties);
  }

  /**
   * Get projects.
   */
  public function getProjects(array $properties = []) {
    $properties += [
      'status' => NodeInterface::PUBLISHED,
      'type' => 'project_main_page',
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
    $hearing_type = $this->getReference($entity, 'field_type');
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
        'lokalplaner' => $lokalplaner,
        'geojson' => $geojson,
      ],
      'links' => [
        'self' => $this->generateUrl('hoeringsportal_data.api_controller_hearings_show', ['hearing' => $entity->id()]),
      ],
    ];
  }

  /**
   * Serialize Hearing as GeoJSON.
   */
  public function serializeGeoJsonHearing(NodeInterface $hearing) {
    $areas = $hearing->get('field_area')->referencedEntities();
    /** @var \Drupal\taxonomy\Entity\Term $hearing_type */
    $hearing_type = $this->getReference($hearing, 'field_type');
    $project = $this->getReference($hearing, 'field_project_reference');
    $ticketCount = $this->getHearingTicketsCount($hearing);

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
        'hearing_replies_count' => $ticketCount,
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
   * Serialize Public meeting as GeoJSON.
   */
  public function serializeGeoJsonPublicMeeting(NodeInterface $meeting) {
    $areas = $meeting->get('field_area')->referencedEntities();
    $types = $meeting->get('field_type')->referencedEntities();
    $type = reset($types);

    return [
      'properties' => [
        'meeting_id' => (int) $meeting->id(),
        'meeting_title' => $meeting->getTitle(),
        'meeting_url' => $this->generateUrl('entity.node.canonical', ['node' => $meeting->id()]),
        'meeting_teaser' => $meeting->get('field_teaser')->value,
        'meeting_description' => $meeting->get('field_description')->value,
        'meeting_area_list' => $this->listify(array_map(function (Term $term) {
          return $term->get('field_area_id')->value;
        }, $areas)),
        'meeting_area_ids' => array_map(function (Term $term) {
          return (int) $term->get('field_area_id')->value;
        }, $areas),
        'meeting_type' => NULL !== $type ? $this->getTermName($type) : NULL,
      ],
    ];
  }

  /**
   * Serialize Public meeting date as GeoJSON.
   */
  public function serializeGeoJsonPublicMeetingDate(object $date) {
    $serialized = $this->serializeGeoJsonPublicMeeting($date->meeting);

    $properties = &$serialized['properties'];
    $data = json_decode(json_encode($date->data), FALSE);
    $properties += [
      'date_uuid' => $data->uuid,
      'date_location' => $data->location,
      'date_address' => $data->address,
      'date_time_from' => $this->getDrupalDateTime($data->time_from_value),
      'date_time_to' => $this->getDrupalDateTime($data->time_to_value),
      'date_spots' => (int) $data->spots,
    ];

    if (isset($data->data->coordinates)) {
      $serialized['geometry'] = [
        'type' => 'Point',
        'coordinates' => $data->data->coordinates,
      ];
      $serialized['type'] = 'Feature';
    }

    return $serialized;
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
   * Get hearing tickets count.
   *
   * @see DeskproHearingHelper::getHearingTicketsCount().
   */
  public function getHearingTicketsCount(NodeInterface $node): int {
    return $this->deskproHelper->getHearingTicketsCount($node);
  }

  /**
   * Get hearing tickets.
   *
   * @see DeskproHearingHelper::getHearingTickets().
   */
  public function getHearingTickets(NodeInterface $node): ?array {
    return $this->deskproHelper->getHearingTickets($node);
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
  private function getTermName(Term|bool|null $term = NULL) {
    return $term ? $term->get('name')->value : NULL;
  }

  /**
   * Get formattted date time.
   */
  private function getDateTime($value) {
    return $value ? (new \DateTime($value))->format(\DateTime::ATOM) : NULL;
  }

  /**
   * Get formatted date time.
   */
  private function getDrupalDateTime($value) {
    return $value ? (new DrupalDateTime($value))->format(\DateTime::ATOM) : NULL;
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

  /**
   * Load entities.
   *
   * @param $entityTypeId
   *   The entity type id.
   * @param array $properties
   *   The entity properties.
   * @param array $orderBy
   *   The order by (field => ASC|DESC)
   * @param null $limit
   *   The limit
   * @param null $offset
   *   The offset
   * @return array|\Drupal\Core\Entity\EntityInterface[]
   *   The loaded entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function loadEntities($entityTypeId, array $properties = [], array $orderBy = [], $limit = null, $offset = null)
  {
    $storage = $this->entityTypeManager
      ->getStorage($entityTypeId);
    $query = $storage->getQuery();
    $query->accessCheck();
    foreach ($properties as $field => $value) {
      // Cast scalars to array so we can consistently use an IN condition.
      $query->condition($field, (array)$value, 'IN');
    }
    foreach ($orderBy as $field => $direction) {
      $query->sort($field, $direction);
    }
    if (null !== $limit) {
      $query->range($offset ?? 0, $limit);
    }
    $result = $query->execute();

    return $result ? $storage->loadMultiple($result) : [];
  }

}
