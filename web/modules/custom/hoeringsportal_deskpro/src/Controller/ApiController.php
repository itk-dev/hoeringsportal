<?php

namespace Drupal\hoeringsportal_deskpro\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\hoeringsportal_deskpro\Exception\DeskproException;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ApiController.
 */
class ApiController extends ControllerBase {

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
   * Get all hearings.
   */
  public function hearings() {
    $hearings = $this->deskpro->getHearings();

    $data = $hearings->getData();
    foreach ($data['choices'] as &$hearing) {
      $hearing['@url'] = $this->getUrlGenerator()->generateFromRoute(
        'hoeringsportal_deskpro.api_controller_hearings_tickets',
        ['hearing' => $hearing['id']],
        ['absolute' => TRUE]
      );
    }

    return new JsonResponse($data);
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
        $ticket['person'] = $this->getPerson($ticket['person']);
      }

      return new JsonResponse($data);
    }
    catch (DeskproException $exception) {
      $message = $exception->getMessage() ?? ($exception->getPrevious() ? $exception->getPrevious()->getMessage() : 'unknown error');

      return new JsonResponse(['error' => $message]);
    }
  }

  /**
   * Get ticket attachments.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response.
   */
  public function ticketAttachments($ticket) {
    try {
      $attachements = $this->deskpro->getTicketAttachments($ticket);
      $data = $attachements->getData();

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

  /**
   * Cached persons.
   *
   * @var array
   */
  private $persons = [];

  /**
   * Get a person by id.
   */
  private function getPerson($id) {
    if (isset($this->persons[$id])) {
      return $this->persons[$id];
    }

    $person = $this->deskpro->getPerson($id);
    $data = $person->getData();

    $this->persons[$id] = $data;

    return $data;
  }

}
