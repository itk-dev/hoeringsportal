<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

use Drupal\Core\Url;

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

    $contentUrl = Url::fromRoute(
      'hoeringsportal_deskpro.hearing.tickets.render',
      [
        'node' => $node->id(),
      ]
    )->toString();

    $configuration = [
      'container_id' => 'hearing-tickets',
      'content_url' => $contentUrl,
      'deadline_passed' => $this->helper->isDeadlinePassed($node),
    ];

    return [
      '#theme' => 'hoeringsportal_hearing_tickets',
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
      '#cache' => ['contexts' => ['url']],
    ];
  }

}
