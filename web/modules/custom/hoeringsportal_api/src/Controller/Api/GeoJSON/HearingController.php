<?php

namespace Drupal\hoeringsportal_api\Controller\Api\GeoJSON;

use Drupal\hoeringsportal_api\Controller\Api\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
class HearingController extends ApiController {

  /**
   *
   */
  public function index() {
    $entities = $this->helper()->getHearings(['nid' => 8]);
    $features = array_values(array_map([$this->helper(), 'serializeGeoJSON'], $entities));

    $response = new JsonResponse([
      'features' => $features,
      'type' => 'FeatureCollection',
    ]);
    $response->headers->set('content-type', 'application/geo+json');

    return $response;
  }

  /**
   *
   */
  public function show($hearing) {
    $entities = $this->helper()->getHearings(['nid' => $hearing]);

    if (1 !== \count($entities)) {
      return new JsonResponse([
        'error' => 'Not found',
      ], 400);
    }

    $entity = \reset($entities);

    $data = $this->helper()->serializeGeoJSON($entity);

    $response = new JsonResponse([
      'features' => [$data],
      'type' => 'FeatureCollection',
    ]);
    $response->headers->set('content-type', 'application/geo+json');

    return new $response();
  }

  /**
   *
   */
  public function tickets($hearing) {
  }

}
