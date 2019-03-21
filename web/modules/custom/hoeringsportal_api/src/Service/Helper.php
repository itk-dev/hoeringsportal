<?php

namespace Drupal\hoeringsportal_api\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;

/**
 *
 */
class Helper {
  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface*/
  private $urlGenerator;

  /**
   * @var \Drupal\hoeringsportal_api\Service\GeoJSONHelper*/
  private $geoJSONHelper;

  /**
   * Constructs a new DeskproHelper object.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, UrlGeneratorInterface $urlGenerator, GeoJSONHelper $geoJSONHelper) {
    $this->entityTypeManager = $entityTypeManager;
    $this->urlGenerator = $urlGenerator;
    $this->geoJSONHelper = $geoJSONHelper;
  }

  /**
   *
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
   *
   */
  public function getGeojson(NodeInterface $entity) {
    return \json_decode($entity->get('field_map_display')->value, TRUE);
  }

  /**
   *
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
   *
   */
  public function serializeGeoJSON(NodeInterface $entity) {
    $areas = $entity->get('field_area')->referencedEntities();
    /** @var \Drupal\taxonomy\Entity\Term $hearing_type */
    $hearing_type = $this->getReference($entity, 'field_hearing_type');
    $project_reference = $this->getReference($entity, 'field_project_reference');
    $tags = $entity->get('field_tags')->referencedEntities();

    $lokalplaner = [];
    foreach ($entity->get('field_lokalplaner') as $lokalplan) {
      $lokalplaner[] = $lokalplan->id;
    }

    $geometry = $this->getGeometry($entity);

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
      ],
      'geometry' => $geometry,
      'type' => 'Feature',
    ];
  }

  /**
   *
   */
  private function getGeometry(NodeInterface $entity) {
    $value = $entity->get('field_map')->getValue();
    if (empty($value) || !isset($value[0]['data'])) {
      return NULL;
    }

    $geojson = \json_decode($value[0]['data'], TRUE);

    return $this->geoJSONHelper->featureCollectionToMulti($geojson);
  }

  /**
   *
   */
  private function getReference($entity, $field_name) {
    $reference = $entity->get($field_name)->first();

    return NULL !== $reference ? $reference->get('entity')->getTarget()->getValue() : NULL;
  }

  /**
   *
   */
  private function getTermName(Term $term = NULL) {
    return $term ? $term->get('name')->value : NULL;
  }

  /**
   *
   */
  private function getDateTime($value) {
    return $value ? (new \DateTime($value))->format(\DateTime::ATOM) : NULL;
  }

  /**
   *
   */
  public function generateUrl($name, $parameters = [], $options = []) {
    $options += [
      'absolute' => TRUE,
    ];

    return $this->urlGenerator->generateFromRoute($name, $parameters, $options);
  }

}
