<?php

namespace Drupal\hoeringsportal_deskpro\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Drupal\node\Controller\NodeViewController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Hearing controller.
 */
class HearingController extends NodeViewController {
  /**
   * Drupal\hoeringsportal_deskpro\Service\DeskproService definition.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\DeskproService
   */
  protected $deskpro;

  /**
   * Constructs a new DeskproController object.
   */
  public function __construct(DeskproService $deskpro, EntityManagerInterface $entity_manager, RendererInterface $renderer, AccountInterface $current_user = NULL) {
    parent::__construct($entity_manager, $renderer, $current_user);
    $this->deskpro = $deskpro;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_deskpro.deskpro'),
      $container->get('entity.manager'),
      $container->get('renderer'),
      $container->get('current_user')
    );
  }

  /**
   * Add ticket in Hearing context.
   */
  public function addTicket(EntityInterface $node, $view_mode = 'full', $langcode = NULL) {
    $build = parent::view($node, $view_mode, $langcode);
    $build['#view_mode'] = 'ticket_add';

    return $build;
  }

  /**
   * Add ticket title.
   */
  public function addTicketTitle(EntityInterface $node) {
    return __METHOD__ . ' ' . parent::title($node);
  }

  /**
   * View ticket in Hearing context.
   */
  public function viewTicket(EntityInterface $node, $ticket, $view_mode = 'full', $langcode = NULL) {
    $build = parent::view($node, $view_mode, $langcode);
    // $build['#theme'] = 'hearing_ticket';.
    $build['#view_mode'] = 'ticket_view';

    $ticket = $this->getTicket($ticket);
    $ticket_messages = $this->getTicketMessages($ticket);
    $ticket_attachments = $this->getTicketAttachments($ticket);

    $build['#ticket'] = $ticket;
    $build['#ticket_messages'] = $ticket_messages;
    $build['#ticket_attachments'] = $ticket_attachments;

    // unset($build['#node']);.
    return $build;
  }

  /**
   * View ticket title.
   */
  public function viewTicketTitle(EntityInterface $node, $ticket) {
    return __METHOD__ . ' ' . parent::title($node);
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
