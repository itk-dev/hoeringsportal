<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

/**
 * Provides a 'Hearing ticket' Block.
 *
 * @Block(
 *   id = "hoeringsportal_deskpro_hearingticket_block",
 *   admin_label = @Translation("Hearing ticket"),
 *   category = @Translation("Deskpro"),
 * )
 */
class HearingTicketBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');
    $ticket = $this->routeMatch->getParameter('ticket');

    if (!$this->helper->isHearing($node) || empty($ticket)) {
      return NULL;
    }

    return [
      '#theme' => 'hoeringsportal_hearing_ticket',
      '#node' => $node,
      '#is_deadline_passed' => $this->helper->isDeadlinePassed($node),
      '#ticket' => $this->helper->getHearingTicket($node, $ticket),
    ];
  }

}
