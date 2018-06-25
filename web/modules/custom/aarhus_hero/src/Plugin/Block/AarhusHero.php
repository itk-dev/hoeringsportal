<?php
/**
 * @file
 * Implements hero block
 */

namespace Drupal\aarhus_hero\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;

/**
 * Provides hero content.
 *
 * @Block(
 *   id = "aarhus_hero",
 *   admin_label = @Translation("Aarhus hero"),
 * )
 */
class AarhusHero extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = \Drupal::getContainer()->get('aarhus_hero.hero_config')->getAll();
    $file = ($config['hero_image']) ? File::load($config['hero_image']) : FALSE;
    
    return array(
      '#type' => 'markup',
      '#theme' => 'aarhus_hero_block',
      '#config' => $config,
      '#image' => array (
        '#theme' => 'image_style',
        '#style_name' => 'hero',
        '#uri' => !empty($file) ? $file->getFileUri() : FALSE,
        '#alt' => $config['hero_title'],
        '#attributes' => array (
          'class' => 'hero__media'
        ),
      ),
    );
  }

}
