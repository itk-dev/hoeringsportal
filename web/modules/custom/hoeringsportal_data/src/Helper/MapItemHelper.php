<?php

namespace Drupal\hoeringsportal_data\Helper;

use Drupal\hoeringsportal_data\Plugin\Field\FieldType\LocalplanItem;
use Drupal\hoeringsportal_data\Plugin\Field\FieldType\MapItem;
use Drupal\hoeringsportal_data\Service\Plandata;
use Drupal\node\NodeInterface;

/**
 * Map item helper.
 */
class MapItemHelper {
  /**
   * The plandata service.
   *
   * @var \Drupal\hoeringsportal_data\Service\Plandata
   */
  private $plandata;

  /**
   * Constructor.
   */
  public function __construct(Plandata $plandata) {
    $this->plandata = $plandata;
  }

  /**
   * Update map data on a node.
   */
  public function updateData(NodeInterface $node) {
    $fields = $node->getFieldDefinitions();
    foreach ($fields as $field) {
      if (MapItem::FIELD_TYPE === $field->getType()) {
        /** @var \Drupal\hoeringsportal_data\Plugin\Field\FieldType\MapItem $item */
        $item = $node->{$field->getName()};
        $data = NULL;
        switch ($item->type) {
          case MapItem::TYPE_GEOJSON:
            $data = json_decode($item->geojson, TRUE);
            break;

          case MapItem::TYPE_LOCALPLANIDS:
            $ids = preg_split('/[, ]+/', $item->localplanids, -1, PREG_SPLIT_NO_EMPTY);
            $data = $this->plandata->getGeojsonFromIds('planid', $ids);
            break;

          case MapItem::TYPE_LOCALPLANIDS_NODE:
            $ids = $this->getLocalplanIds($node);
            $data = $this->plandata->getGeojsonFromIds('planid', $ids);
            break;
        }

        $item->data = json_encode($data);
      }
    }
  }

  /**
   * Decide if a node has a hoeringsportal_data_localplan field.
   */
  public static function hasLocalplanField(NodeInterface $node) {
    foreach ($node->getFieldDefinitions() as $field) {
      if (LocalplanItem::FIELD_TYPE === $field->getType()) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Get list of local plan ids on a node.
   */
  private function getLocalplanIds(NodeInterface $node) {
    $ids = [];
    if (self::hasLocalplanField($node)) {
      foreach ($node->getFieldDefinitions() as $fieldDefinition) {
        if (LocalplanItem::FIELD_TYPE === $fieldDefinition->getType()) {
          $field = $node->get($fieldDefinition->getName());
          foreach ($field->getValue() as $item) {
            if ($item['id']) {
              $ids[] = $item['id'];
            }
          }
        }
      }
    }

    return $ids;
  }

}
