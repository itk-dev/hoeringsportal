<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

/**
 * Provides a 'Hearing ticket title' Block.
 *
 * @Block(
 *   id = "hoeringsportal_deskpro_hearingticket_title_block",
 *   admin_label = @Translation("Hearing ticket title"),
 *   category = @Translation("Deskpro"),
 * )
 */
class HearingTicketTitleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');
    $ticket = $this->routeMatch->getParameter('ticket');

    if (!$this->helper->isHearing($node)) {
      return NULL;
    }

    $cacheTags = $node->getCacheTags();

    $cacheContexts = $node->getCacheContexts();
    $cacheContexts[] = 'url';
    $cacheContexts = array_unique($cacheContexts);

    return [
      '#theme' => 'hoeringsportal_hearing_ticket_title',
      '#node' => $node,
      '#ticket' => !empty($ticket) ? $this->helper->getHearingTicket($node, $ticket) : NULL,
      '#cache' => [
        'contexts' => $cacheContexts,
        'tags' => $cacheTags,
      ],
    ];
  }

}
