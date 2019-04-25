<?php

namespace Drupal\hoeringsportal_data\Controller\Api\GeoJSON;

use Drupal\hoeringsportal_data\Controller\Api\ApiController;

/**
 * GeoJson ticket controller.
 */
class TicketController extends ApiController {

  /**
   * Get tickets.
   */
  public function index() {
    $entities = $this->getTickets();
    $features = array_values(array_map([$this->helper(), 'serializeGeoJsonTicket'], $entities));
    $response = $this->createGeoJsonResponse($features);

    return $response;
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

}
