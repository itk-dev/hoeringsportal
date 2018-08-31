<?php

namespace Drupal\hoeringsportal_deskpro\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AutocompleteController.
 */
class AutocompleteController extends ControllerBase {

  /**
   * Drupal\hoeringsportal_deskpro\Service\DeskproService definition.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\DeskproService
   */
  protected $deskpro;

  /**
   * Constructs a new DeskproController object.
   */
  public function __construct(DeskproService $deskpro) {
    $this->deskpro = $deskpro;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_deskpro.deskpro')
    );
  }

  /**
   * Department.
   */
  public function department() {
    $departments = $this->deskpro->getTicketDepartments(['no_cache' => 1]);

    $data = array_map(function (array $department) {
      return [
        'value' => $department['id'],
        'label' => sprintf('%s (%s)', $department['title'], $department['id']),
      ];
    }, $departments->getData());

    return new JsonResponse($data);
  }

}
