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
      $field_reply_deadline = $node->field_reply_deadline->getValue();
      $start_date = strtotime($node->field_start_date->getValue()['0']['value']);
      // Set reply deadline.
      $reply_deadline = isset($field_reply_deadline) ? strtotime($field_reply_deadline['0']['value']) : FALSE;
      $current_date = time();
      if ($reply_deadline) {
        // Calculate if we should show a warning.
        if ($reply_deadline - $current_date < $deadline_hours * 3600 || $current_date < $start_date) {
          $config['not_started'] = ($current_date < $start_date) ? TRUE : FALSE;
          $config['deadline_passed'] = ($current_date > $reply_deadline) ? TRUE : FALSE;
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
