<?php
/**
 * @file
 * Implements hotness levels block
 */

namespace Drupal\hoeringsportal_hotness\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides hotness levels content.
 *
 * @Block(
 *   id = "hoeringsportal_hotness_levels",
 *   admin_label = @Translation("Hoeringsportal hotness levels"),
 * )
 */
class HoeringsportalHotnessLevels extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#type' => 'markup',
      '#theme' => 'hoeringsportal_hotness_levels_block',
    );
  }
}
