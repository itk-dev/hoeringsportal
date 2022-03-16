<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

use Drupal\Core\Datetime\DrupalDateTime;

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
    $start_date = isset($node->field_start_date->date) ? $node->field_start_date->date->getTimestamp() : FALSE;
    $now = new DrupalDateTime('now');
    $now_timestamp = $now->getTimestamp();
    $is_hearing_started = (!empty($start_date) && $now_timestamp > $start_date) ? TRUE : FALSE;
    if (!$this->helper->isHearing($node)) {
      return NULL;
    }

    return [
      '#theme' => 'hoeringsportal_hearing_tickets',
      '#node' => $node,
      '#is_deadline_passed' => $this->helper->isDeadlinePassed($node),
      '#tickets' => $this->helper->getDeskproTickets($node),
      '#is_hearing_started' => $is_hearing_started,
    ];
  }

}
