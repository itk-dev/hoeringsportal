<?php

namespace Drupal\hoeringsportal_deskpro\Twig;

use Drupal\Core\Entity\EntityInterface;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;

/**
 * Custom Twig extensions for Høringsportal.
 */
class TwigExtension extends \Twig_Extension {
  /**
   * The Deskpro service.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\DeskproService
   */
  private $deskpro;

  /**
   * {@inheritdoc}
   */
  public function __construct(DeskproService $deskpro) {
    $this->deskpro = $deskpro;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('deskpro_ticket_form', [$this, 'getTicketForm'], ['is_safe' => ['all']]),
      new \Twig_SimpleFunction('deskpro_get_ticket_stuff', [$this, 'getTicketStuff']),
    ];
  }

  /**
   * Get ticket form.
   */
  public function getTicketForm(EntityInterface $node) {
    try {
      if ($node->bundle() !== 'hearing') {
        throw new \Exception('Node of type hearing expected. Got' . $node->bundle());
      }
      $departmentId = 1;
      $hearingId = 1;
      $defaultValues = [];

      $user = \Drupal::currentUser();
      if ($user->isAuthenticated()) {
        $defaultValues['ticket']['person']['user_name'] = $user->getAccountName();
        $defaultValues['ticket']['person']['user_email']['email'] = $user->getEmail();
      }

      $form = $this->deskpro->getTicketEmbedForm($departmentId, $hearingId, $defaultValues);

      return $form;
    }
    catch (\Exception $e) {
      return '<pre style="background: red; padding: 1em; color: yellow">' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
  }

  /**
   * Get ticket stuff. HACK!
   */
  public function getTicketStuff() {
    $parameters = \Drupal::routeMatch()->getParameters();

    $ticket = $this->getTicket($parameters->get('ticket'));
    $ticket_messages = $this->getTicketMessages($ticket);
    $ticket_attachments = $this->getTicketAttachments($ticket);

    return [$ticket, $ticket_messages, $ticket_attachments];
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
