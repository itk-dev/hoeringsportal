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

/**
 * Class DataController.
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
   * Constructs a new DeskproController object.
   */
  public function __construct(DeskproService $deskpro, HearingHelper $helper) {
    $this->deskpro = $deskpro;
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_deskpro.deskpro'),
      $container->get('hoeringsportal_deskpro.helper')
    );
  }

  /**
   * Hearing tickets.
   */
  public function syncronizeHearing(Request $request) {
    try {
      $data = json_decode($request->getContent(), TRUE);
      $result = $this->helper->syncronizeHearing($data);

      return new JsonResponse($result);
    }
    catch (\Exception $e) {
      return new JsonResponse(['message' => $e->getMessage()], 400);
    }
  }

  /**
   * Syncronize hearing data from Deskpro.
   *
   * @see https://www.drupal.org/node/2122195
   */
  public function syncronizeHearingAccess(Request $request) {
    $token = $request->headers->get('x-deskpro-token');
    return $this->deskpro->isValidToken($token) ? new AccessResultAllowed() : new AccessResultForbidden();
  }

}
