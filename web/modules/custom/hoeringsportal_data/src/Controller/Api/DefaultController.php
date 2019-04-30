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
          'projects' => $this->generateUrl('hoeringsportal_data.api_controller_geojson_projects'),
          'hearings' => $this->generateUrl('hoeringsportal_data.api_controller_geojson_hearings'),
          'tickets' => $this->generateUrl('hoeringsportal_data.api_controller_geojson_tickets'),
        ],
      ],
    ]);
  }

}
