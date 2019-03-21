<?php

namespace Drupal\hoeringsportal_api\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
class TicketController extends ApiController {

  /**
   *
   */
  public function index() {
    $entities = $this->getTickets();

    $data = array_values(array_map([$this, 'serialize'], $entities));

    return new JsonResponse([
      'data' => $data,
      'links' => [
        'self' => $this->generateUrl('hoeringsportal_api.api_controller_tickets'),
      ],
    ]);
  }

  /**
   *
   */
  private function getTickets() {
    $hearings = $this->helper()->getHearings();

    $tickets = [];
    foreach ($hearings as $hearing) {
      $data = \json_decode($hearing->get('field_deskpro_data')->value);
      if (isset($data->tickets)) {
        foreach ($data->tickets as $ticket) {
          $tickets[] = (object) [
            'data' => $ticket,
            'hearing' => $hearing,
          ];
        }
      }
    }

    return $tickets;
  }

  /**
   *
   */
  private function serialize(object $entity) {
    $hearing = $entity->hearing;
    $data = $entity->data;

    // header('Content-type: text/plain'); echo var_export($entity->data->fields, true); die(__FILE__.':'.__LINE__.':'.__METHOD__);.
    return [
      'properties' => [
        'id' => $entity->id,
        'ref' => $entity->ref,
        'person' => $entity->person,
        'message' => $entity->fields->message ?? NULL,

        'organization' => $entity->fields->organization ?? NULL,
        'address' => $entity->fields->address ?? NULL,
        'geolocation' => $entity->fields->geolocation ?? NULL,
        'hearing' => $this->helper()->serializeHearing($hearing),

    // 'areas' => array_map([$this, 'getTermName'], $areas),
    //        'content_state' => $entity->get('field_content_state')->value,
    //        'description' => $entity->get('field_description')->value,
    //        'hearing_type' => $this->getTermName($hearing_type),
    //        'project_reference' => $project_reference ? $project_reference->getTitle() : NULL,
    //        'reply_deadline' => $this->getDateTime($entity->get('field_reply_deadline')->value),
    //        'start_date' => $this->getDateTime($entity->get('field_start_date')->value),
    //        'tags' => array_map([$this, 'getTermName'], $tags),
    //        'teaser' => $entity->get('field_teaser')->value,
    //        'lokalplaner' => $lokalplaner,
    //        'geojson' => $geojson,.
      ],
    ];
  }

}
