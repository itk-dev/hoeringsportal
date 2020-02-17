<?php

namespace Drupal\hoeringsportal_deskpro\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
  public function department(Request $request) {
    $query = $request->get('q');
    $departments = $this->deskpro->getTicketDepartments(['no_cache' => 1]);

    $matches = array_values(array_filter(
      $departments->getData(),
      static function ($department) use ($query) {
        return FALSE !== stripos($department['title'], $query);
      }
    ));

    // Return all if no matches are found.
    if (empty($matches)) {
      $matches = $departments->getData();
    }

    $data = array_map(
      static function (array $department) {
        return [
          'value' => $department['id'],
          'label' => sprintf('%s (%s)', $department['title'], $department['id']),
        ];
      },
      $matches
    );

    return new JsonResponse($data);
  }

  /**
   * Agent.
   */
  public function agent(Request $request) {
    $query = $request->get('q');
    $agents = $this->deskpro->getAgents(['no_cache' => 1]);

    $matches = array_values(array_filter(
      $agents->getData(),
      static function ($agent) use ($query) {
        return FALSE !== stripos($agent['primary_email'], $query)
          || FALSE !== stripos($agent['name'], $query);
      }
    ));

    // Return all if no matches are found.
    if (empty($matches)) {
      $matches = $agents->getData();
    }

    $data = array_map(
      static function (array $agent) {
        return [
          'value' => $agent['primary_email'],
          'label' => sprintf('%s (%s)', $agent['name'], $agent['primary_email']),
        ];
      },
      $matches
    );

    return new JsonResponse($data);
  }

}
