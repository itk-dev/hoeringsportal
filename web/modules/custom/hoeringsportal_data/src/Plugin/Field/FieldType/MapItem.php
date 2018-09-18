<?php

namespace Drupal\hoeringsportal_data\Plugin\Field\FieldType;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;

/**
 * Field type "hoeringsportal_data_map".
 *
 * @FieldType(
 *   id = "hoeringsportal_data_map",
 *   label = @Translation("Map display"),
 *   category = @Translation("Hoeringsportal"),
 *   default_widget = "hoeringsportal_data_map_default",
 *   default_formatter = "hoeringsportal_data_map_default",
 * )
 */
class MapItem extends FieldItemBase {
  const FIELD_TYPE = 'hoeringsportal_data_map';
  const TYPE_GEOJSON = 'geojson';
  const TYPE_LOCALPLANIDS = 'localplanids';
  const TYPE_LOCALPLANIDS_NODE = 'localplanids_node';

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'type' => [
          'type' => 'varchar',
          'length' => 255,
        ],
        'data' => [
          'type' => 'text',
          'size' => 'big',
        ],
        'geojson' => [
          'type' => 'text',
          'size' => 'big',
        ],
        'localplanids' => [
          'type' => 'varchar',
          'length' => 255,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    return [
      'type' => DataDefinition::create('string')
        ->setLabel(t('Type'))
        ->setRequired(TRUE),

      'data' => DataDefinition::create('string')
        ->setLabel(t('Data'))
        ->setRequired(FALSE),

      'geojson' => DataDefinition::create('string')
        ->setLabel(t('GeoJSON'))
        ->setRequired(FALSE),

      'localplanids' => DataDefinition::create('string')
        ->setLabel(t('GeoJSON'))
        ->setRequired(FALSE),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $item = $this->getValue();

    return !isset($item['type']);
  }

  /**
   * Get map data.
   */
  public function getMapData() {
    return json_decode($this->data, TRUE);
  }

}
