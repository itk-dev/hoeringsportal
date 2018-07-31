<?php

namespace Drupal\hoeringsportal_deskpro\Controller;

use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Render a ticket.
   */
  public function ticket($hearing, $ticket) {
    $hearing = Node::load($hearing);
    $ticket = $this->getTicket($ticket);
    $ticket_messages = $this->getTicketMessages($ticket);
    $ticket_attachments = $this->getTicketAttachments($ticket);

    return [
      '#theme' => 'hoeringsportal_deskpro_ticket',
      '#hearing' => $hearing,
      '#ticket' => $ticket,
      '#ticket_messages' => $ticket_messages,
      '#ticket_attachments' => $ticket_attachments,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  /**
   * Get a ticket.
   */
  private function getTicket($ticket) {
    $ticket = $this->deskpro->getTicket($ticket)->getData();
    $ticket['person'] = $this->getPerson($ticket['person']);

    return $ticket;
  }

  /**
   * Get ticket messages.
   */
  private function getTicketMessages($ticket) {
    $messages = $this->deskpro->getTicketMessages($ticket['id'])->getData();
    foreach ($messages as &$message) {
      $message['person'] = $this->getPerson($message['person']);
      if (!empty($message['attachments'])) {
        $attachments = $this->deskpro->getMessageAttachments($message);
        $message['attachments'] = $attachments !== NULL ? $attachments->getData() : NULL;
      }
    }

    return $messages;
  }

  /**
   * Get ticket attachments.
   */
  private function getTicketAttachments($ticket) {
    $attachments = $this->deskpro->getTicketAttachments($ticket['id'])->getData();

    return $attachments;
  }

  /**
   * Get a person.
   */
  private function getPerson($person) {
    return $this->deskpro->getPerson($person)->getData();
  }

}
