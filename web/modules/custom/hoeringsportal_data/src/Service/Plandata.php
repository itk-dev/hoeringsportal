<?php

namespace Drupal\hoeringsportal_data\Service;

use Drupal\Core\Site\Settings;
use GuzzleHttp\Client;

/**
 * Service for getting data from Plandata.dk.
 */
class Plandata {
  const PDK_LOKALPLAN = 'pdk:lokalplan';
  const PROJECTION_EPSG_3857 = 'EPSG:3857';
  const PROJECTION_EPSG_4326 = 'EPSG:4326';
  const OUTPUT_FORMAT_JSON = 'application/json';

  /**
   * A Url.
   *
   * @var string
   */
  private $wsfUrl;

  /**
   * A query.
   *
   * @var array
   */
  private $wsfBaseQuery;

  /**
   * Constructor.
   */
  public function __construct(Settings $settings) {
    // @todo Make this configurable.
    $this->wsfUrl = 'https://geoserver.plandata.dk/geoserver/wfs';
    $this->wsfBaseQuery = [
      'servicename' => 'wfs',
      'service' => 'wfs',
      'version' => '2.0.0',
    ];
  }

  /**
   * Get GeoJSON from a list of plan ids.
   */
  public function getGeojsonFromIds(string $attribute, array $ids) {
    $response = $this->findLokalplanBy($attribute, $ids);

    return json_decode((string) $response->getBody());
  }

  /**
   * Find a list of local plans by id.
   *
   * Http://docs.geoserver.org/latest/en/user/services/wfs/vendor.html#wfs-vendor-parameters.
   */
  public function findLokalplanBy($property, array $values) {
    $filter = $property . ' IN (' . implode(',', array_map([$this,
      'escapeCqlValue',
    ], $values)) . ')';
    $response = $this->getData([
      'request' => 'GetFeature',
      'typeNames' => self::PDK_LOKALPLAN,
      'srsName' => self::PROJECTION_EPSG_4326,
      'outputFormat' => self::OUTPUT_FORMAT_JSON,
      'cql_filter' => $filter,
    ]);

    return $response;
  }

  /**
   * Escape a value for cql.
   *
   * Http://docs.geoserver.org/latest/en/user/filter/ecql_reference.html#literal.
   */
  private function escapeCqlValue($value) {
    return is_numeric($value) ? $value : "'" . str_replace("'", "''", $value) . "'";
  }

  /**
   * Get data from wsf service.
   */
  public function getData(array $query) {
    $client = new Client([
      'base_uri' => $this->wsfUrl,
    ]);

    $response = $client->get('', [
      'query' => $query + $this->wsfBaseQuery,
    ]);

    return $response;
  }

}
