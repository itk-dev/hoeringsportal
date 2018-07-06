<?php

namespace Drupal\hoeringsportal_deskpro\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\hoeringsportal_deskpro\Exception\DeskproException;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class DeskproController.
 */
class DeskproController extends ControllerBase {

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
   * Tickets.
   *
   * @return string
   *   Return Hello string.
   */
  public function hearingTickets($hearing) {
    try {
      $tickets = $this->deskpro->getTickets($hearing);

      $data = $tickets->getData();
      foreach ($data as &$ticket) {
        $ticket['@url'] = $this->getUrlGenerator()->generateFromRoute(
          'hoeringsportal_deskpro.deskpro_controller_tickets_messages',
          ['ticket' => $ticket['id']],
          ['absolute' => TRUE]
        );
      }

      return new JsonResponse($data);
    }
    catch (DeskproException $exception) {
      $message = $exception->getMessage() ?? ($exception->getPrevious() ? $exception->getPrevious()->getMessage() : 'unknown error');

      return new JsonResponse(['error' => $message]);
    }
  }

  /**
   * Messages.
   *
   * @return string
   *   Return Hello string.
   */
  public function ticketMessages($ticket) {
    try {
      $messages = $this->deskpro->getTicketMessages($ticket);

      $data = $messages->getData();
      foreach ($data as &$message) {
        if (!empty($message['attachments'])) {
          $attachments = $this->deskpro->getMessageAttachments($message);
          $message['attachments'] = $attachments !== NULL ? $attachments->getData() : NULL;
        }
      }

      return new JsonResponse($data);
    }
    catch (DeskproException $exception) {
      $message = $exception->getMessage() ?? ($exception->getPrevious() ? $exception->getPrevious()->getMessage() : 'unknown error');

      return new JsonResponse(['error' => $message]);
    }
  }

}
