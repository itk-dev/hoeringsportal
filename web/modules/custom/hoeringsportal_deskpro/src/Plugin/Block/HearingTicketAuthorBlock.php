<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

/**
 * Provides a 'Hearing ticket author' Block.
 *
 * @Block(
 *   id = "hoeringsportal_deskpro_hearingticket_author_block",
 *   admin_label = @Translation("Hearing ticket author"),
 *   category = @Translation("Deskpro"),
 * )
 */
class HearingTicketAuthorBlock extends HearingTicketBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');
    $ticket = $this->routeMatch->getParameter('ticket');
    $ticket = $this->helper->getDeskproTicket($node, $ticket);

    if (!$this->helper->isHearing($node) || empty($ticket)) {
      throw new NotFoundHttpException();
    }

    $cacheTags = $node->getCacheTags();

    $cacheContexts = $node->getCacheContexts();
    $cacheContexts[] = 'url';
    $cacheContexts = array_unique($cacheContexts);

    return [
      '#theme' => 'hoeringsportal_hearing_ticket_author',
      '#node' => $node,
      '#is_deadline_passed' => $this->helper->isDeadlinePassed($node),
      '#ticket' => $ticket,
      '#cache' => [
        'contexts' => $cacheContexts,
        'tags' => $cacheTags,
      ],
    ];
  }

}
