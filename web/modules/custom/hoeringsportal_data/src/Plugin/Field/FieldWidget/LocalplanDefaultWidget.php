<?php

namespace Drupal\hoeringsportal_data\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Field widget.
 *
 * @FieldWidget(
 *   id = "hoeringsportal_data_localplan_default",
 *   label = @Translation("Localplan default"),
 *   field_types = {
 *     "hoeringsportal_data_localplan",
 *   }
 * )
 */
class LocalplanDefaultWidget extends WidgetBase {

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
    /** @var \Drupal\hoeringsportal_data\Plugin\Field\FieldType\MapItem $item */
    $item =& $items[$delta];

    $element['id'] = [
      '#type' => 'textfield',
      '#default_value' => $item->id ?? '',
    ];

    return $element;
  }

}
