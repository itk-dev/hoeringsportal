<?php

namespace Drupal\hoeringsportal_api\Controller\Api\GeoJSON;

use Drupal\hoeringsportal_api\Controller\Api\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * GeoJSON hearing controller.
 */
class HearingController extends ApiController {

  /**
   * Get hearings.
   */
  public function index() {
    $entities = $this->helper()->getHearings();
    $features = array_values(array_map([$this->helper(), 'serializeGeoJson'], $entities));
    $response = $this->createGeoJsonResponse($features, 'FeatureCollection');

    return $response;
  }

  /**
   * Show a hearing.
   */
  public function show($hearing) {
    $entities = $this->helper()->getHearings(['nid' => $hearing]);

    if (1 !== \count($entities)) {
      return new JsonResponse([
        'error' => 'Not found',
      ], 400);
    }

    $entity = \reset($entities);

    $data = $this->helper()->serializeGeoJson($entity);

    $response = new JsonResponse([
      'features' => [$data],
      'type' => 'FeatureCollection',
    ]);
    $response->headers->set('content-type', 'application/geo+json');

    return new $response();
  }

}
