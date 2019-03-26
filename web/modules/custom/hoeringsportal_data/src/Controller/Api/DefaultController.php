<?php

namespace Drupal\hoeringsportal_data\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Default controller.
 */
class DefaultController extends ApiController {

  /**
   * Show api endpoints.
   */
  public function index() {
    return new JsonResponse([
      'resources' => [
        'geojson' => [
          'hearings_plandata' => $this->generateUrl('hoeringsportal_data.api_controller_geojson_hearings', ['type' => 'plandata']),
          'hearings_point' => $this->generateUrl('hoeringsportal_data.api_controller_geojson_hearings', ['type' => 'point']),
          'tickets' => $this->generateUrl('hoeringsportal_data.api_controller_geojson_tickets'),
        ],
      ],
    ]);
  }

}
