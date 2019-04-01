<?php

namespace Drupal\hoeringsportal_data\Controller\Api;

use Drupal\hoeringsportal_data\Helper\GeoJsonHelper;
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
          'hearings_local_plan' => $this->generateUrl('hoeringsportal_data.api_controller_geojson_hearings', ['geometry' => GeoJsonHelper::GEOMETRY_LOCAL_PLAN]),
          'hearings_point' => $this->generateUrl('hoeringsportal_data.api_controller_geojson_hearings', ['geometry' => GeoJsonHelper::GEOMETRY_POINT]),
          'tickets' => $this->generateUrl('hoeringsportal_data.api_controller_geojson_tickets'),
        ],
      ],
    ]);
  }

}
