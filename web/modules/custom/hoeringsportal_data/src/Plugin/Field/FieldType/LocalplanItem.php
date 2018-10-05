<?php

namespace Drupal\hoeringsportal_data\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Field type "hoeringsportal_data_localplan".
 *
 * @FieldType(
 *   id = "hoeringsportal_data_localplan",
 *   label = @Translation("Local plan"),
 *   category = @Translation("Hoeringsportal"),
 *   default_widget = "hoeringsportal_data_localplan_default",
 *   default_formatter = "hoeringsportal_data_localplan_default",
 * )
 */
class LocalplanItem extends FieldItemBase {
  const FIELD_TYPE = 'hoeringsportal_data_localplan';

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'id' => [
          'type' => 'varchar',
          'length' => 255,
        ],

        'data' => [
          'type' => 'text',
          'size' => 'big',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    return [
      'id' => DataDefinition::create('string')
        ->setLabel(t('Type'))
        ->setRequired(FALSE),

      'data' => DataDefinition::create('string')
        ->setLabel(t('Data'))
        ->setRequired(FALSE),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $item = $this->getValue();

    return empty($item['id']);
  }

  /**
   * Get stuff.
   */
  public function getStuff() {
    return json_decode($this->data, TRUE) ?? [];
  }

}
