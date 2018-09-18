<?php

namespace Drupal\hoeringsportal_data\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\hoeringsportal_data\Helper\MapItemHelper;
use Drupal\hoeringsportal_data\Plugin\Field\FieldType\MapItem;

/**
 * Field widget.
 *
 * @FieldWidget(
 *   id = "hoeringsportal_data_map_default",
 *   label = @Translation("Map default"),
 *   field_types = {
 *     "hoeringsportal_data_map",
 *   }
 * )
 */
class MapDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // $item is where the current saved values are stored.
    /** @var \Drupal\hoeringsportal_data\Plugin\Field\FieldType\MapItem $item */
    $item =& $items[$delta];
    $parentNameSelector = preg_replace('@\.([0-9]+)\.?@', '[\1]', $item->getPropertyPath());

    $element += [
      '#type' => 'fieldset',
    ];

    $typeOptions = [
      MapItem::TYPE_GEOJSON => t('GeoJSON'),
      MapItem::TYPE_LOCALPLANIDS => t('Local plan ids'),
    ];
    if (MapItemHelper::hasLocalplanField($items->getEntity())) {
      $typeOptions[MapItem::TYPE_LOCALPLANIDS_NODE] = t('Local plan ids from node');
    }
    $element['type'] = [
      '#type' => 'select',
      '#title' => t('Type'),
      '#options' => $typeOptions,
      '#empty_value' => '',
      '#default_value' => $item->type ?? '',
    ];
    $geojsonUrl = 'http://geojson.io/';
    $geojsonUrlWithMap = $geojsonUrl . '#map=13/56.1464/10.1739';
    // @see https://github.com/mapbox/geojson.io/blob/gh-pages/API.md#datadataapplicationjson
    $geojsonUrlWithData = $geojsonUrl . '#data=data:application/json,' . urlencode(json_encode(json_decode($item->geojson)) ?? '');
    $element['geojson'] = [
      '#type' => 'textarea',
      '#title' => t('GeoJSON'),
      '#default_value' => $item->geojson ?? '',
      '#description' => t('Use <a href="@url_with_data">@url</a> and copy the generated GeoJSON code into this fields.', [
        '@url' => $geojsonUrl,
        '@url_with_data' => $geojsonUrlWithData,
      ]),
      '#states' => [
        'visible' => [
          ':input[name="' . $parentNameSelector . '[type]"]' => ['value' => MapItem::TYPE_GEOJSON],
        ],
      ],
    ];
    $element['localplanids'] = [
      '#type' => 'textfield',
      '#title' => t('localplanids'),
      '#default_value' => $item->localplanids ?? '',
      '#description' => t('<a href="@url">@url</a>', ['@url' => 'https://visplaner.plandata.dk/visplaner/lokalplaner.html']),
      '#states' => [
        'visible' => [
          ':input[name="' . $parentNameSelector . '[type]"]' => ['value' => MapItem::TYPE_LOCALPLANIDS],
        ],
      ],
    ];

    return $element;
  }

}
