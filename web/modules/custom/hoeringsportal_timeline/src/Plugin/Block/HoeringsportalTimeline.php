<?php

namespace Drupal\hoeringsportal_timeline\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides timeline content.
 *
 * @Block(
 *   id = "hoeringsportal_timeline",
 *   admin_label = @Translation("Hoeringsportal timeline"),
 * )
 */
class HoeringsportalTimeline extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = \Drupal::getContainer()->get('hoeringsportal_timeline.settings')->getAll();
    $nid = \Drupal::routeMatch()->getParameter('node')->id();

    if (isset($config['node_ref']) && $nid == $config['node_ref']) {
      return [
        '#type' => 'markup',
        '#theme' => 'hoeringsportal_timeline_block',
        '#config' => $config,
      ];
    }
    return NULL;
  }

}
