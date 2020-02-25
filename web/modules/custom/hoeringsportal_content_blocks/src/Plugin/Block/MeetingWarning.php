<?php

namespace Drupal\hoeringsportal_content_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides meeting warning block content.
 *
 * @Block(
 *   id = "meeting_warning",
 *   admin_label = @Translation("Node public meeting warning"),
 * )
 */
class MeetingWarning extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get current node.
    $node = \Drupal::routeMatch()->getParameter('node');
    if (isset($node) && $node->field_content_state->value) {
      $config['deadline_passed'] = $node->field_content_state->value;
      return [
        '#type' => 'markup',
        '#theme' => 'hoeringsportal_meeting_warning',
        '#config' => $config,
      ];
    }
    return NULL;
  }

}
