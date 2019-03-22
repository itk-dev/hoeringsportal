<?php

namespace Drupal\hoeringsportal_api\Service;

/**
 * GeoJSON helper.
 */
class GeoJsonHelper {

  /**
   * Merge a GeoJSON feature collection into a single multi geometry.
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

      if ($isMulti) {
        $coordinates = \array_merge($coordinates, $geometry['coordinates']);
      }
      else {
        $coordinates[] = $geometry['coordinates'];
      }
    }

    return [
      'type' => $isMulti ? $type : 'Multi' . $type,
      'coordinates' => $coordinates,
    ];
  }

}
