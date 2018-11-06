<?php

namespace Drupal\hoeringsportal_project_timeline\Plugin\Block;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides timeline content.
 *
 * @Block(
 *   id = "project_timeline",
 *   admin_label = @Translation("Project timeline"),
 * )
 */
class ProjectTimeline extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if (!$node) {
      return;
    }
    $nid = $node->id();
    $now = new DrupalDateTime('now');
    $now_timestamp = $now->getTimestamp();

    // Add start and end date to timeline items.
    $project_start_timestamp = $node->field_project_start->date->getTimestamp();
    $project_end_timestamp = $node->field_project_finish->date->getTimestamp();
    $timeline_items = [
      [
        'title' => t('Project start'),
        'startDate' => $node->field_project_start->value,
        'endDate' => NULL,
        'type' => 'system',
        'state' => $project_start_timestamp < $now_timestamp ? 'passed' : 'upcomming',
        'nid' => 0,
        'link' => NULL,
      ],
      [
        'title' => t('Expected end date'),
        'startDate' => $node->field_project_finish->value,
        'endDate' => NULL,
        'type' => 'system',
        'state' => $project_end_timestamp < $now_timestamp ? 'passed' : 'upcomming',
        'nid' => 0,
        'link' => NULL,
      ],
    ];

    // Add hearings to timeline items.
    $query = \Drupal::entityQuery('node');
    $query->condition('field_project_reference', $nid);
    $query->condition('type', 'hearing');
    $entity_ids = $query->execute();
    if (!empty($entity_ids)) {
      $hearings = Node::loadMultiple($entity_ids);
      foreach ($hearings as $hearing) {
        if (isset($hearing->values['field_reply_deadline'])) {
          $timeline_items[] = [
            'title' => $hearing->title->value,
            'startDate' => $hearing->field_reply_deadline->value,
            'endDate' => NULL,
            'type' => 'hearing',
            'state' => $hearing->field_reply_deadline->date->getTimestamp() < $now_timestamp ? 'passed' : 'upcomming',
            'nid' => $hearing->nid->value,
            'link' => NULL,
          ];
        }
      }
    }

    // Add paragraph field values to timeline.
    foreach ($node->field_timeline_items->getValue() as $paragraph_item) {
      $paragraph_obj = Paragraph::load($paragraph_item['target_id']);
      if (isset($paragraph_obj->values['field_timeline_date'])) {
        $timeline_items[] = [
          'title' => $paragraph_obj->field_timeline_title->value,
          'startDate' => $paragraph_obj->field_timeline_date->value,
          'endDate' => NULL,
          'type' => $paragraph_obj->field_timeline_type->value,
          'state' => $paragraph_obj->field_timeline_date->date->getTimestamp() < $now_timestamp ? 'passed' : 'upcomming',
          'nid' => NULL,
          'link' => $paragraph_obj->field_timeline_link->uri,
        ];
      }
    }

    return [
      '#theme' => 'hoeringsportal_project_timeline',
      '#node' => $node,
      '#attached' => [
        'library' => [
          'hoeringsportal_project_timeline/project_timeline',
        ],
        'drupalSettings' => [
          'timelineItems' => $timeline_items,
        ],
      ],
    ];
  }

}
