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
      foreach ($this->helper()->getHearingTickets($hearing) as $ticket) {
        $tickets[] = (object) [
          'data' => $ticket,
          'hearing' => $hearing,
        ];
      }
    }

    return $tickets;
  }

}
