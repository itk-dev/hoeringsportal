<?php

namespace Drupal\hoeringsportal_data\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Field formatter.
 *
 * @FieldFormatter(
 *   id = "hoeringsportal_data_map_default",
 *   label = @Translation("Map default"),
 *   field_types = {
 *     "hoeringsportal_data_map",
 *   }
 * )
 */
class MapDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $output = [];

    foreach ($items as $delta => $item) {
      $build['type'] = [
        '#markup' => $item->type,
      ];
      // $build['geojson'] = $item->geojson;.
      $output[$delta] = $build;
    }

    return $output;
  }

}
