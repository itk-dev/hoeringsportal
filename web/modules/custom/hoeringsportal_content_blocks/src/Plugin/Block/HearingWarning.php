<?php

namespace Drupal\hoeringsportal_content_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides hearing warning block content.
 *
 * @Block(
 *   id = "hearing_warning",
 *   admin_label = @Translation("Node hearing warning"),
 * )
 */
class HearingWarning extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    $reply_deadline = strtotime($node->field_reply_deadline->getValue()['0']['value']);
    $current_date = time();
    $config['reply_deadline'] = $node->field_reply_deadline->getValue()['0']['value'];
    if ($reply_deadline - $current_date < 604800) {
      $config['deadline_passed'] = ($current_date > $reply_deadline) ? TRUE : FALSE;
      return [
        '#type' => 'markup',
        '#theme' => 'hoeringsportal_hearing_warning',
        '#config' => $config,
      ];
    }
    return FALSE;
  }

}
