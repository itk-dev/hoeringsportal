<?php

namespace Drupal\hoeringsportal_api\Controller\Api;

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
        'hearings' => $this->generateUrl('hoeringsportal_api.api_controller_hearings'),
        'tickets' => $this->generateUrl('hoeringsportal_api.api_controller_tickets'),
        'geojson' => [
          'hearings_plandata' => $this->generateUrl('hoeringsportal_api.api_controller_geojson_hearings', ['type' => 'plandata']),
          'hearings_point' => $this->generateUrl('hoeringsportal_api.api_controller_geojson_hearings', ['type' => 'point']),
          'tickets' => $this->generateUrl('hoeringsportal_api.api_controller_geojson_tickets'),
        ],
      ],
    ]);
  }

}
