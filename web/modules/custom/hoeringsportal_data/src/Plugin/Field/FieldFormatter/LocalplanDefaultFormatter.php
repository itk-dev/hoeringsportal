<?php

namespace Drupal\hoeringsportal_data\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Field formatter.
 *
 * @FieldFormatter(
 *   id = "hoeringsportal_data_localplan_default",
 *   label = @Translation("Localplan default"),
 *   field_types = {
 *     "hoeringsportal_data_localplan",
 *   }
 * )
 */
class LocalplanDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $output = [];

    foreach ($items as $delta => $item) {
      $build['id'] = ['#markup' => '<strong>' . $item->id . '</strong>'];
      // $build['data'] = ['#markup' => $item->data];.
      $build['data'] = ['#markup' => 'hest'];

      $output[$delta] = $build;
    }

    return $output;
  }

}
