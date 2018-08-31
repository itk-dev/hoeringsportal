<?php

namespace Drupal\hoeringsportal_quicklinks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides quicklinks content.
 *
 * @Block(
 *   id = "quicklinks",
 *   admin_label = @Translation("Quicklinks"),
 * )
 */
class Quicklinks extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    return [
      '#theme' => 'hoeringsportal_quicklinks',
      '#node' => $node,
      '#attached' => [
        'library' => [
          'hoeringsportal_quicklinks/quicklinks',
        ],
      ],
    ];
  }

}
