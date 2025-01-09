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
          'hearings' => $this->generateUrl('hoeringsportal_data.api_controller_geojson_hearings'),
          'public_meeting_dates' => $this->generateUrl('hoeringsportal_data.api_controller_geojson_public_meeting_dates'),
          'v2' => [
            'hearings' => $this->generateUrl('hoeringsportal_data.api_geojson_v2_hearings'),
          ],
        ],
      ],
    ]);
  }

}
