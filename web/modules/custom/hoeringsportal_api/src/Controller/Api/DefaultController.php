<?php

namespace Drupal\hoeringsportal_api\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 */
class DefaultController extends ApiController {

  /**
   *
   */
  public function index() {
    return new JsonResponse([
      'resources' => [
        'hearings' => $this->generateUrl('hoeringsportal_api.api_controller_hearings'),
        'tickets' => $this->generateUrl('hoeringsportal_api.api_controller_tickets'),
      ],
    ]);
  }

}
