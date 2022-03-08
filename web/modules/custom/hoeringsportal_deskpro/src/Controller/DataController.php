<?php

namespace Drupal\hoeringsportal_deskpro\Controller;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Data controller.
 */
class DataController extends ControllerBase implements AccessInterface {

  /**
   * The Deskpro service.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\DeskproService
   */
  protected $deskpro;

  /**
   * The hearing helper.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\HearingHelper
   */
  protected $helper;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new DeskproController object.
   */
  public function __construct(DeskproService $deskpro, HearingHelper $helper, RequestStack $requestStack) {
    $this->deskpro = $deskpro;
    $this->helper = $helper;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_deskpro.deskpro'),
      $container->get('hoeringsportal_deskpro.helper'),
      $container->get('request_stack')
    );
  }

  /**
   * Synchronize ticket.
   */
  public function synchronizeTicket(Request $request) {
    try {
      $payload = json_decode($request->getContent(), TRUE);
      $result = $this->helper->synchronizeTicket($payload, TRUE);

      return new JsonResponse($result);
    }
    catch (\Exception $e) {
      return new JsonResponse(['message' => $e->getMessage()], 400);
    }
  }

  /**
   * Synchronize hearing data from Deskpro.
   *
   * @see https://www.drupal.org/node/2122195
   * but note that we cannot get the Request directly.
   */
  public function accessCheck() {
    $token = $this->requestStack->getCurrentRequest()->headers->get('x-deskpro-token');
    return $this->deskpro->isValidToken($token) ? new AccessResultAllowed() : new AccessResultForbidden('Invalid data token');
  }

}
