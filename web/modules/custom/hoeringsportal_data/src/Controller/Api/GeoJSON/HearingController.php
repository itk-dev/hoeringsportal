<?php

namespace Drupal\hoeringsportal_data\Controller\Api\GeoJSON;

use Drupal\hoeringsportal_data\Controller\Api\ApiController;
use Drupal\hoeringsportal_data\Helper\GeoJsonHelper;
use Drupal\hoeringsportal_data\Plugin\Field\FieldType\MapItem;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * GeoJSON hearing controller.
 */
class HearingController extends ApiController {

  /**
   * Get hearings.
   */
  public function index() {
    $geometry = $this->getParameter('geometry');

    $conditions = [];
    switch ($geometry) {
      case GeoJsonHelper::GEOMETRY_POINT:
        $conditions['field_map.type'] = [MapItem::TYPE_ADDRESS];
        break;

      case GeoJsonHelper::GEOMETRY_LOCAL_PLAN:
        $conditions['field_map.type'] = [MapItem::TYPE_LOCALPLANIDS_NODE, MapItem::TYPE_LOCALPLANIDS];
        break;
    }

    $entities = $this->helper()->getHearings($conditions);

    $features = array_values(array_map([$this->helper(), 'serializeGeoJsonHearing'], $entities));
    $response = $this->createGeoJsonResponse($features, 'FeatureCollection');

    return $response;
  }

  /**
   * Show a hearing.
   */
  public function show($hearing) {
    if (!is_numeric($hearing)) {
      return $this->index($hearing);
    }

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
