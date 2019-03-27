<?php

namespace Drupal\hoeringsportal_api\Controller\Api;

use Drupal\Core\Controller\ControllerBase;
use Drupal\hoeringsportal_api\Service\Helper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Api controller.
 */
abstract class ApiController extends ControllerBase {
  /**
   * Helper.
   *
   * @var \Drupal\hoeringsportal_api\Service\Helper
   */
  private $helper;

  /**
   * Constructor.
   */
  public function __construct(Helper $hearingHelper) {
    $this->helper = $hearingHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_api.helper')
    );
  }

  /**
   * Get the helper.
   */
  protected function helper() {
    return $this->helper;
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
