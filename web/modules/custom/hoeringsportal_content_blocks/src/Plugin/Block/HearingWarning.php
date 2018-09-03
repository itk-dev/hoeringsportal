<?php

namespace Drupal\hoeringsportal_content_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DrupalDateTime;

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
    // Default on week.
    $deadline_hours = 168;
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('hoeringsportal_config_settings')) {
      $deadline_hours = (int) \Drupal::getContainer()->get('itk_admin.itk_config')->get('hearing_warning_timer');

      // If no hearing warning is set.
      if ($deadline_hours == 0) {
        $deadline_hours = 168;
      }
    }
    // Get current node.
    $node = \Drupal::routeMatch()->getParameter('node');
    if (isset($node) && $node->hasField('field_reply_deadline')) {
      // Set reply deadline.
      $reply_deadline = $node->field_reply_deadline->date->getTimestamp();
      $start_date = isset($node->field_start_date->value) ? $node->field_start_date->date->getTimestamp() : FALSE;
      $now = new DrupalDateTime('now');
      $now_timestamp = $now->getTimestamp();
      // Set template variables.
      if ($reply_deadline) {
        // Calculate if we should show a warning.
        if ($reply_deadline - $now_timestamp < $deadline_hours * 3600 || $now_timestamp < $start_date) {
          $config['not_started'] = ($now_timestamp < $start_date) ? TRUE : FALSE;
          $config['deadline_passed'] = ($now_timestamp > $reply_deadline) ? TRUE : FALSE;
          return [
            '#type' => 'markup',
            '#theme' => 'hoeringsportal_hearing_warning',
            '#config' => $config,
          ];
        }
      }
    }
    return NULL;
  }

}
