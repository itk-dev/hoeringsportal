<?php

namespace Drupal\hoeringsportal_data\Helper;

use Drupal\hoeringsportal_data\Plugin\Field\FieldType\LocalplanItem;
use Drupal\hoeringsportal_data\Service\Plandata;
use Drupal\node\NodeInterface;

/**
 * Local plan helper.
 */
class LocalplanItemHelper {
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
   * Update node with data from plandata service.
   */
  public function updateData(NodeInterface $node) {
    $fields = $node->getFieldDefinitions();
    foreach ($fields as $field) {
      if (LocalplanItem::FIELD_TYPE === $field->getType()) {
        /** @var \Drupal\Core\Field\FieldItemListInterface $item */
        $items = $node->{$field->getName()};
        // @TODO: Improve this by only making a single call with all ids.
        foreach ($items as $item) {
          $data = $this->plandata->getGeojsonFromIds('planid', [$item->id]);
          $item->data = json_encode($data);
        }
      }
    }
  }

}
