<?php

namespace Drupal\hoeringsportal_data\Controller\Api\V2\GeoJSON;

use Drupal\hoeringsportal_data\Controller\Api\ApiController;
use Drupal\hoeringsportal_data\Helper\GeoJsonHelper;
use Drupal\hoeringsportal_data\Plugin\Field\FieldType\MapItem;

/**
 * GeoJSON hearing controller.
 */
class HearingController extends ApiController {

  /**
   * Get hearings.
   */
  public function index() {
    $geometry = $this->getParameter('geometry');
    $page = max(1, $this->getParameter('page', 1));
    $pageSize = min(50, $this->getParameter('page_size', 50));

    $conditions = [];
    switch ($geometry) {
      case GeoJsonHelper::GEOMETRY_POINT:
        $conditions['field_map.type'] = [MapItem::TYPE_POINT];
        break;

      case GeoJsonHelper::GEOMETRY_LOCAL_PLAN:
        $conditions['field_map.type'] = [MapItem::TYPE_LOCALPLANIDS_NODE, MapItem::TYPE_LOCALPLANIDS];
        break;
    }

    // We get one more entity that actually needed to check if we have a "next" relation.
    $entities = $this->helper()->getHearings($conditions, ['created' => 'DESC'], $pageSize + 1, $pageSize * ($page - 1));
    $hasNext = count($entities) > $pageSize;
    array_pop($entities);
    $entities = array_slice($entities, 0, $pageSize);

    $features = array_map($this->helper()->serializeGeoJsonHearing(...), $entities);
    $response = $this->createGeoJsonResponse(
      $features,
      cacheContexts: ['url.query_args:geometry', 'url.query_args:page', 'url.query_args:page_size'],
      cacheTags: ['node_list:hearing'],
    );

    $this->addRels($response, $page, $hasNext);

    return $response;
  }

}
