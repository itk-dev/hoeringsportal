<?php

namespace Drupal\hoeringsportal_data\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Hearing controller.
 */
class HearingController extends ApiController {

  /**
   * Index.
   */
  public function index() {
    $entities = $this->helper()->getHearings();
    $data = array_values(array_map([$this, 'serialize'], $entities));

    return new JsonResponse([
      'data' => $data,
      'links' => [
        'self' => $this->generateUrl('hoeringsportal_data.api_controller_hearings'),
      ],
    ]);
  }

  /**
   * Show a Hearing.
   */
  public function show($hearing) {
    $entities = $this->helper()->getHearings(['nid' => $hearing]);

    if (1 !== \count($entities)) {
      return new JsonResponse([
        'error' => 'Not found',
      ], 400);
    }

    $entity = \reset($entities);

    $data = $this->serializer->serialize($entity, 'json');

    return new JsonResponse($data);
  }

  /**
   * Show tickets on a hearing.
   */
  public function tickets($hearing) {
    return new JsonResponse(NULL);
  }

}
