<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

/**
 * Provides a 'Hearing tickets' Block.
 *
 * @Block(
 *   id = "hoeringsportal_deskpro_hearingtickets_block",
 *   admin_label = @Translation("Hearing tickets"),
 *   category = @Translation("Deskpro"),
 * )
 */
class HearingTicketsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->routeMatch->getParameter('node');

    if (!$this->helper->isHearing($node)) {
      return NULL;
    }

    return [
      '#theme' => 'hoeringsportal_hearing_tickets',
      '#node' => $node,
      '#is_deadline_passed' => $this->helper->isDeadlinePassed($node),
      '#tickets' => $this->helper->getHearingTickets($node),
    ];
  }

}
