<?php

namespace Drupal\hoeringsportal_api\Controller\Api;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Zend\Diactoros\Response\JsonResponse;

/**
 *
 */
class HearingController extends ApiController {

  /**
   *
   */
  public function index() {
    $entities = $this->entityTypeManager()
      ->getStorage('node')
      ->loadByProperties(['type' => 'hearing']);

    $data = array_values(array_map([$this, 'serialize'], $entities));

    return new JsonResponse([
      'data' => $data,
      'links' => [
        'self' => $this->generateUrl('hoeringsportal_api.api_controller_hearings'),
      ],
    ]);
  }

  /**
   *
   */
  public function show($hearing) {
    $entities = $this->entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'type' => 'hearing',
        'nid' => $hearing,
      ]);

    if (1 !== \count($entities)) {
      return new JsonResponse([
        'error' => 'Not found',
      ], 400);
    }

    $entity = \reset($entities);

    $data = $this->serialize($entity);

    return new JsonResponse($data);
  }

  /**
   *
   */
  public function tickets($hearing) {
  }

  /**
   *
   */
  private function serialize(Node $entity) {
    $areas = $entity->get('field_area')->referencedEntities();
    /** @var \Drupal\taxonomy\Entity\Term $hearing_type */
    $hearing_type = $this->getReference($entity, 'field_hearing_type');
    $project_reference = $this->getReference($entity, 'field_project_reference');
    $tags = $entity->get('field_tags')->referencedEntities();

    return [
      'properties' => [
        'id' => $entity->id(),
        'title' => $entity->getTitle(),
        'area' => array_map([$this, 'getTermName'], $areas),
        'content_state' => $entity->get('field_content_state')->value,
        'description' => $entity->get('field_description')->value,
        'hearing_type' => $this->getTermName($hearing_type),
        'project_reference' => $project_reference ? $project_reference->getTitle() : NULL,
        'reply_deadline' => $this->getDateTime($entity->get('field_reply_deadline')->value),
        'start_date' => $this->getDateTime($entity->get('field_start_date')->value),
        'tags' => array_map([$this, 'getTermName'], $tags),
        'teaser' => $entity->get('field_teaser')->value,
      ],
      'links' => [
        'self' => $this->generateUrl('hoeringsportal_api.api_controller_hearings_show', ['hearing' => $entity->id()]),
      ],
    ];
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

}
