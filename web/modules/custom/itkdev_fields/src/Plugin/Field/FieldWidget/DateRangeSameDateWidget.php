<?php

namespace Drupal\itkdev_fields\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime_range\Plugin\Field\FieldWidget\DateRangeDefaultWidget;

/**
 * Field widget.
 *
 * @FieldWidget(
 *   id = "daterange_same_date",
 *   label = @Translation("Same date"),
 *   field_types = {
 *     "daterange"
 *   }
 * )
 */
class DateRangeSameDateWidget extends DateRangeDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // $element['#widget'] =
    // $element['value']['#widget'] =
    // $element['end_value']['#widget'] = $this->getPluginId();
    // $element['#theme_wrappers'][] = $this->getPluginId();
    $element['#theme'] = 'widget_' . $this->getPluginId();

    return $element;
  }

}
