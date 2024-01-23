<?php

namespace Drupal\hoeringsportal_content_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides address block content.
 *
 * @Block(
 *   id = "content_address",
 *   admin_label = @Translation("Node address"),
 * )
 */
class HearingTypeAddress extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if (isset($node) && $node->hasField('field_hearing_type')) {
      $value = $node->field_hearing_type->getValue();
      if (isset($value[0]['target_id'])) {
        $term_id = $value[0]['target_id'];
        $config['term'] = Term::load($term_id);

        return [
          '#type' => 'markup',
          '#theme' => 'hoeringsportal_hearing_type_address',
          '#config' => $config,
        ];
      }
    }

    return [];
  }

}
