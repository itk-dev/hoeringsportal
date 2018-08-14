<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

use Drupal\Core\Url;

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

    $contentUrl = Url::fromRoute(
      'hoeringsportal_deskpro.hearing.ticket.render',
      [
        'node' => $node->id(),
        'ticket' => $ticket,
      ]
    )->toString();

    $configuration = [
      'container_id' => 'hearing-ticket',
      'content_url' => $contentUrl,
    ];

    return [
      '#theme' => 'hoeringsportal_hearing_ticket',
      '#node' => $node,
      '#is_loading' => TRUE,
      '#configuration' => $configuration,
      '#attached' => [
        'drupalSettings' => [
          'deskpro_hoeringsportal' => $configuration,
        ],
        'library' => [
          'hoeringsportal_deskpro/load_content',
        ],
      ],
    ];
  }

}
