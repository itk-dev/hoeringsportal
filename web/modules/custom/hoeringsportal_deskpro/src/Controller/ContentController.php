<?php

namespace Drupal\hoeringsportal_deskpro\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ContentController.
 */
class ContentController extends ControllerBase {

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
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new DeskproController object.
   */
  public function __construct(DeskproService $deskpro, HearingHelper $helper, RendererInterface $renderer) {
    $this->deskpro = $deskpro;
    $this->helper = $helper;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_deskpro.deskpro'),
      $container->get('hoeringsportal_deskpro.helper'),
      $container->get('renderer')
    );
  }

  /**
   * Hearing tickets.
   */
  public function hearingTickets(NodeInterface $node) {
    if (!$this->helper->isHearing($node)) {
      return NULL;
    }

    $configuration = [
      'container_id' => 'hearing-tickets-content',
      'deadline_passed' => $this->helper->isDeadlinePassed($node),
    ];

    $tickets = $this->getTickets($node);
    $build = [
      '#theme' => 'hoeringsportal_hearing_tickets',
      '#node' => $node,
      '#is_loading' => FALSE,
      '#configuration' => $configuration,
      '#tickets' => $tickets,
    ];

    return $this->render($build);
  }

  /**
   * Hearing ticket.
   */
  public function hearingTicket(NodeInterface $node, $ticket) {
    if (!$this->helper->isHearing($node) || empty($ticket)) {
      return NULL;
    }

    $ticket = $this->getTicket($ticket);
    $ticket_messages = $this->getTicketMessages($ticket);
    $ticket_attachments = $this->getTicketAttachments($ticket);

    $build = [
      '#theme' => 'hoeringsportal_hearing_ticket',
      '#ticket' => $ticket,
      '#ticket_messages' => $ticket_messages,
      '#ticket_attachments' => $ticket_attachments,
    ];

    return $this->render($build);
  }

  /**
   * Render a build array.
   */
  private function render(array $build) {
    return new Response($this->renderer->render($build));
  }

  /**
   * Get tickets.
   */
  private function getTickets(NodeInterface $hearing) {
    $tickets = $this->deskpro->getHearingTickets(
      $hearing->field_deskpro_hearing_id->value,
      ['expand' => ['person', 'messages']]
    )->getData();

    return $tickets;
  }

  /**
   * Get a ticket.
   */
  private function getTicket($ticket) {
    $ticket = $this->deskpro->getTicket($ticket, ['expand' => 'person'])->getData();

    return $ticket;
  }

  /**
   * Get ticket messages.
   */
  private function getTicketMessages($ticket) {
    $messages = $this->deskpro->getTicketMessages(
      $ticket['id'],
      ['expand' => 'person,attachments']
    )->getData();

    return $messages;
  }

  /**
   * Get ticket attachments.
   */
  private function getTicketAttachments($ticket) {
    $attachments = $this->deskpro->getTicketAttachments($ticket['id'])->getData();

    return $attachments;
  }

}
