<?php

namespace Drupal\hoeringsportal_data\Controller\Api\GeoJSON;

use Drupal\hoeringsportal_data\Controller\Api\ApiController;
use Drupal\hoeringsportal_data\Plugin\Field\FieldType\MapItem;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * GeoJSON hearing controller.
 */
class HearingController extends ApiController {

  /**
   * Get hearings.
   */
  public function index($type) {
    // By default, we show local plans.
    $conditions = [
      'field_map.type' => [MapItem::TYPE_LOCALPLANIDS_NODE, MapItem::TYPE_LOCALPLANIDS],
    ];
    switch ($type) {
      case 'point':
        $conditions['field_map.type'] = [MapItem::TYPE_ADDRESS];
        break;
    }

    $entities = $this->helper()->getHearings($conditions);

    if ('lokalplaner' === $type) {
      return $this->hearingsJoinLokalplaner($entities);
    }

    $features = array_values(array_map([$this->helper(), 'serializeGeoJsonHearing'], $entities));
    $response = $this->createGeoJsonResponse($features, 'FeatureCollection');

    return $response;
  }

  /**
   * Get join table for Hearings and Lokalplaner.
   */
  public function hearingsJoinLokalplaner(array $hearings) {
    $features = [];

    foreach ($hearings as $hearing) {
      foreach ($hearing->get('field_lokalplaner') as $lokalplan) {
        $features[] = [
          'properties' => [
            'hearing_id' => (int) $hearing->id(),
            'lokalplan_id' => (int) $lokalplan->id,
          ],
        ];
      }
    }

    return $this->createGeoJsonResponse($features, 'FeatureCollection');
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
