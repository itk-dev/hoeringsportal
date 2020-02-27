<?php

namespace Drupal\hoeringsportal_data\Controller\Api\GeoJSON;

use Drupal\hoeringsportal_data\Controller\Api\ApiController;

/**
 * GeoJson ticket controller.
 */
class PublicMeetingController extends ApiController {

  /**
   * Show public meeting dates.
   */
  public function dates() {
    $entities = $this->getDates();
    $features = array_values(array_map([$this->helper(), 'serializeGeoJsonPublicMeetingDate'], $entities));

    return $this->createGeoJsonResponse($features);
  }

  /**
   * Get public meeting dates.
   */
  private function getDates() {
    $meetings = $this->helper()->getPublicMeetings();

    $dates = [];
    foreach ($meetings as $meeting) {
      foreach ($meeting->get('field_pretix_dates')->getValue() as $item) {
        if (isset($item['data']['coordinates'])) {
          $dates[] = (object) [
            'data' => $item,
            'meeting' => $meeting,
          ];
        }
      }
    }

    return $dates;
  }

}
