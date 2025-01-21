<?php

namespace Drupal\hoeringsportal_data\Controller\Api;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\hoeringsportal_data\Helper\GeoJsonHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

/**
 * Api controller.
 */
abstract class ApiController extends ControllerBase {

  /**
   * Constructor.
   */
  final public function __construct(
    protected readonly RequestStack $requestStack,
    protected readonly RouteMatchInterface $routeMatch,
    protected readonly GeoJsonHelper $geoJsonHelper,
    protected readonly Serializer $serializer,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('current_route_match'),
      $container->get('hoeringsportal_data.geojson_helper'),
      $container->get('serializer')
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
  protected function createGeoJsonResponse(array $features, string $type = 'FeatureCollection', ?array $cacheContexts = NULL, ?array $cacheTags = NULL): CacheableJsonResponse {
    $response = new CacheableJsonResponse([
      'features' => array_values(
        array_filter(
          $features,
          static fn (array $item) => isset($item['geometry'])
        )
      ),
      'type' => $type,
    ]);
    $response->headers->set('content-type', 'application/geo+json');

    if ($cacheContexts || $cacheTags) {
      // @see https://www.drupal.org/docs/drupal-apis/cache-api/cache-tags#s-what
      $response->addCacheableDependency(
        (new CacheableMetadata())
          // @see https://www.drupal.org/docs/drupal-apis/cache-api/cache-contexts
          ->setCacheContexts($cacheContexts ?? [])
          ->setCacheMaxAge(Cache::PERMANENT)
          ->setCacheTags($cacheTags ?? [])
      );
    }

    return $response;
  }

  /**
   * Adds rels to response.
   *
   * @see https://datatracker.ietf.org/doc/html/rfc8288
   */
  protected function addRels(Response $response, int $page, bool $hasNext): Response {
    $routeName = $this->routeMatch->getRouteName();
    $rels['self'] = $this->generateUrl($routeName, ['page' => $page]);
    if ($page > 1) {
      $rels['prev'] = $this->generateUrl($routeName, ['page' => $page - 1]);
    }
    if ($hasNext) {
      $rels['next'] = $this->generateUrl($routeName, ['page' => $page + 1]);
    }
    $links = array_map(static fn (string $rel, string $url) => sprintf('<%s>; rel="%s"', $url, $rel), array_keys($rels), array_values($rels));
    $response->headers->add(['link' => implode(', ', $links)]);

    return $response;
  }

}
