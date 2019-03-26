<?php

namespace Drupal\hoeringsportal_data\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Ticket controller.
 */
class TicketController extends ApiController {

  /**
   * Index.
   */
  public function index() {
    $entities = $this->getTickets();

    $data = array_values(array_map([$this, 'serialize'], $entities));

    return new JsonResponse([
      'data' => $data,
      'links' => [
        'self' => $this->generateUrl('hoeringsportal_data.api_controller_tickets'),
      ],
    ]);
  }

  /**
   * Get tickets.
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
   * Serialize a Ticket.
   */
  private function serialize(object $entity) {
    $hearing = $entity->hearing;
    $data = $entity->data;

    return [
      'properties' => [
        'id' => $data->id,
        'ref' => $data->ref,
        'message' => $data->fields->message ?? NULL,
        'person_name' => $data->person->name ?? NULL,
        'organization' => $data->fields->organization ?? NULL,
        'hearing' => $this->helper()->serializeHearing($hearing),
      ],
    ];
  }

}
