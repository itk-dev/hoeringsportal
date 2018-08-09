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
    if (isset($node) && $node->hasField('field_reply_deadline')) {
      $field_reply_deadline = $node->field_reply_deadline->getValue();
      $reply_deadline = isset($field_reply_deadline) ? strtotime($field_reply_deadline['0']['value']) : FALSE;
      $current_date = time();
      if ($reply_deadline) {
        if ($reply_deadline - $current_date < 604800) {
          $config['deadline_passed'] = ($current_date > $reply_deadline) ? TRUE : FALSE;
          return [
            '#type' => 'markup',
            '#theme' => 'hoeringsportal_hearing_warning',
            '#config' => $config,
          ];
        }
      }
    }
    return FALSE;
  }

}
