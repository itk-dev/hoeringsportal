<?php

namespace Drupal\hoeringsportal_api\Service;

/**
 *
 */
class GeoJSONHelper {

  /**
   *
   */
  public function featureCollectionToMulti(array $geojson) {
    if (!isset($geojson['type']) || 'FeatureCollection' !== $geojson['type']) {
      return $geojson;
    }

    $type = NULL;
    $isMulti = FALSE;
    $coordinates = [];

    foreach ($geojson['features'] as $feature) {
      $geometry = $feature['geometry'];
      if (NULL === $type) {
        $type = $geometry['type'];
        $isMulti = preg_match('/^Multi/', $type);
      }
      elseif ($type !== $geometry['type']) {
        // Different type in feature.
        continue;
      }

      $coordinates[] = $geometry['coordinates'];
    }

    header('content-type: application/json'); echo \json_encode([
      'type' => $isMulti ? $type : 'Multi' . $type,
      'coordinates' => $coordinates,
    ], TRUE); die();

    return [
      'type' => $isMulti ? $type : 'Multi' . $type,
      'coordinates' => $coordinates,
    ];
  }

}
