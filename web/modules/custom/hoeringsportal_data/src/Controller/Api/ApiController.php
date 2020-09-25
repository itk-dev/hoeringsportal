<?php

namespace Drupal\hoeringsportal_data\Controller\Api;

use Drupal\Core\Controller\ControllerBase;
use Drupal\hoeringsportal_data\Helper\GeoJsonHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * TODO: Description of what the class does.
 */
abstract class ApiController extends ControllerBase {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Helper.
   *
   * @var \Drupal\hoeringsportal_data\Helper\GeoJsonHelper
   */
  private $geoJsonHelper;

  /**
   * Constructor.
   */
  public function __construct(RequestStack $requestStack, GeoJsonHelper $geoJsonHelper) {
    $this->requestStack = $requestStack;
    $this->geoJsonHelper = $geoJsonHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('hoeringsportal_data.geojson_helper')
    );
  }

  /**
   * Get a query parameter.
   */
  protected function getParameter($key, $default = NULL) {
    return $this->requestStack->getCurrentRequest()->get($key, $default);
  }

  /**
   * Get the helper.
   */
  protected function helper() {
    return $this->geoJsonHelper;
  }

  /**
   * Generate a url.
   */
  protected function generateUrl($name, $parameters = [], $options = []) {
    return $this->helper()->generateUrl($name, $parameters, $options);
  }

  /**
   * Create a GeoJSON response.
   */
  protected function createGeoJsonResponse(array $features, string $type = 'FeatureCollection') {
    $response = new JsonResponse([
      'features' => $features,
      'type' => 'FeatureCollection',
    ]);
    $response->headers->set('content-type', 'application/geo+json');

    return $response;
  }

}
