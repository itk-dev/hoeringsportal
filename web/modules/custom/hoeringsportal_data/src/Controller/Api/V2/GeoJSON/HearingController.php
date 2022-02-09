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
    $entities = $this->helper()->getHearings($conditions, ['created' => 'DESC'], $pageSize+1, $pageSize *($page-1));
    $hasNext = count($entities) > $pageSize;
    array_pop($entities);
    $entities = array_slice($entities, 0, $pageSize);

    $features = array_values(array_map([$this->helper(), 'serializeGeoJsonHearing'], $entities));
    $response = $this->createGeoJsonResponse($features, 'FeatureCollection');

    // @see https://datatracker.ietf.org/doc/html/rfc8288
    $rels['self'] = $this->generateUrl('hoeringsportal_data.api_geojson_v2_hearings', ['page' => $page]);
    if ($page > 1) {
      $rels['prev'] = $this->generateUrl('hoeringsportal_data.api_geojson_v2_hearings', ['page' => $page-1]);
    }
    if ($hasNext) {
      $rels['next'] = $this->generateUrl('hoeringsportal_data.api_geojson_v2_hearings', ['page' => $page + 1]);
    }
    $links = array_map(function ($rel, $url) { return sprintf('<%s>; rel="%s"', $url, $rel); }, array_keys($rels), array_values($rels));
    $response->headers->add(['link' => implode(', ', $links)]);

    return $response;
  }

}
