<?php

namespace Drupal\hoeringsportal_api\Controller\Api;

use Zend\Diactoros\Response\JsonResponse;

/**
 *
 */
class HearingController extends ApiController {

  /**
   *
   */
  public function index() {
    $entities = $this->helper()->getHearings();
    $data = array_values(array_map([$this, 'serialize'], $entities));

    return new JsonResponse([
      'data' => $data,
      'links' => [
        'self' => $this->generateUrl('hoeringsportal_api.api_controller_hearings'),
      ],
    ]);
  }

  /**
   *
   */
  public function show($hearing) {
    $entities = $this->helper()->getHearings(['nid' => $hearing]);

    if (1 !== \count($entities)) {
      return new JsonResponse([
        'error' => 'Not found',
      ], 400);
    }

    $entity = \reset($entities);

    $data = $this->serialize($entity);

    return new JsonResponse($data);
  }

  /**
   *
   */
  public function tickets($hearing) {
  }

}
