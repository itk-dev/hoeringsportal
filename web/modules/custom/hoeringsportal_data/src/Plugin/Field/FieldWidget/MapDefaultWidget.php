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
      MapItem::TYPE_LOCALPLANIDS => t('Local plan ids'),
      MapItem::TYPE_POINT => t('Point'),
    ];
    if (MapItemHelper::hasLocalplanField($items->getEntity())) {
      $typeOptions[MapItem::TYPE_LOCALPLANIDS_NODE] = t('Local plan ids from node');
    }
    asort($typeOptions);
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
    $element[MapItem::TYPE_POINT] = [
      '#type' => 'hidden',
      '#default_value' => $item->point ?? '',
    ];

    $element[MapItem::TYPE_POINT . '-widget'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          ':input[name="' . $parentNameSelector . '[type]"]' => ['value' => MapItem::TYPE_POINT],
        ],
      ],

      'septima-widget' => [
        '#type' => 'container',
        '#title' => __METHOD__,
        '#description' => t('Enter address or coordinates (e.g. "56.1535557,10.2120222")'),
        '#attributes' => [
          'class' => ['septima-widget'],
          'data-value' => $item->point ?? '',
          'data-value-target' => '[name="' . $parentNameSelector . '[' . MapItem::TYPE_POINT . ']',
        ],
      ],
    ];

    $element['#attached']['library'][] = 'hoeringsportal_data/hearing-edit';

    return $element;
  }

}
