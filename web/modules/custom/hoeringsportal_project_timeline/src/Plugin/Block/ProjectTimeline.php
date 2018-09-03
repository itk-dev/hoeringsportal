<?php

namespace Drupal\hoeringsportal_project_timeline\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides timeline content.
 *
 * @Block(
 *   id = "project_timeline",
 *   admin_label = @Translation("Project timeline"),
 * )
 */
class ProjectTimeline extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    return [
      '#theme' => 'hoeringsportal_project_timeline',
      '#node' => $node,
      '#attached' => [

        'library' => [
          'hoeringsportal_project_timeline/project_timeline',
        ],
      ],
    ];
  }

}
