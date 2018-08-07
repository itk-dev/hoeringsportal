<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Hearing ticket' Block.
 *
 * @Block(
 *   id = "hoeringsportal_deskpro_hearingticket_block",
 *   admin_label = @Translation("Hearing ticket"),
 *   category = @Translation("Deskpro"),
 * )
 */
class HearingTicketBlock extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Deskpro client.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\DeskproService
   */
  protected $deskpro;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('hoeringsportal_deskpro.deskpro')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $routeMatch,
    DeskproService $deskpro
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $routeMatch;
    $this->deskpro = $deskpro;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');
    $ticket = $this->routeMatch->getParameter('ticket');

    if ('hearing' !== $node->bundle() || empty($ticket)) {
      return NULL;
    }

    $ticket = $this->getTicket($ticket);
    $ticket_messages = $this->getTicketMessages($ticket);
    $ticket_attachments = $this->getTicketAttachments($ticket);

    return [
      '#theme' => 'block__hoeringsportal_hearing_ticket',
      '#ticket' => $ticket,
      '#ticket_messages' => $ticket_messages,
      '#ticket_attachments' => $ticket_attachments,
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
