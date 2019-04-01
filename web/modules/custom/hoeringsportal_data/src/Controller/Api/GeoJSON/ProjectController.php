<?php

namespace Drupal\hoeringsportal_data\Controller\Api\GeoJSON;

use Drupal\hoeringsportal_data\Controller\Api\ApiController;
use Drupal\hoeringsportal_data\Helper\GeoJsonHelper;
use Drupal\hoeringsportal_data\Plugin\Field\FieldType\MapItem;

/**
 * GeoJSON project controller.
 */
class ProjectController extends ApiController {

  /**
   * Get projects.
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

    $entities = $this->helper()->getProjects($conditions);

    $features = array_values(array_map([$this->helper(), 'serializeGeoJsonProject'], $entities));
    $response = $this->createGeoJsonResponse($features, 'FeatureCollection');

    return $response;
  }

}
