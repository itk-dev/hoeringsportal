<?php

namespace Drupal\hoeringsportal_data\Helper;

use Drupal\hoeringsportal_data\Plugin\Field\FieldType\LocalplanItem;
use Drupal\hoeringsportal_data\Plugin\Field\FieldType\MapItem;
use Drupal\hoeringsportal_data\Service\DAWA;
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
   * The DAWA service.
   *
   * @var \Drupal\hoeringsportal_data\Service\DAWA
   */
  private $dawa;

  /**
   * Constructor.
   */
  public function __construct(Plandata $plandata, DAWA $dawa) {
    $this->plandata = $plandata;
    $this->dawa = $dawa;
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
        $geojson = NULL;
        switch ($item->type) {
          case MapItem::TYPE_GEOJSON:
            $geojson = json_decode($item->geojson, TRUE);
            break;

          case MapItem::TYPE_LOCALPLANIDS:
            $ids = preg_split('/[, ]+/', $item->localplanids, -1, PREG_SPLIT_NO_EMPTY);
            $geojson = $this->plandata->getGeojsonFromIds('planid', $ids);
            break;

          case MapItem::TYPE_LOCALPLANIDS_NODE:
            $ids = $this->getLocalplanIds($node);
            $geojson = $this->plandata->getGeojsonFromIds('planid', $ids);
            break;

          case MapItem::TYPE_ADDRESS:
            $coordinates = NULL;
            if (preg_match('/(?P<lat>[0-9.]+)\s*,(?P<lng>\s*[0-9.]+)/', $item->address, $matches)) {
              $coordinates = [$matches['lat'], $matches['lng']];
            }
            else {
              $coordinates = $this->dawa->getCoordinates($item->address);
            }
            $geojson = [
              'properties' => [
                'address' => $item->address,
              ],
            ];
            if (NULL !== $coordinates) {
              $geojson['type'] = 'Feature';
              $geojson['geometry'] = [
                'type' => 'Point',
                'coordinates' => $coordinates,
              ];
            }
            break;
        }

        $item->data = json_encode($geojson);
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
