<?php

namespace Drupal\hoeringsportal_project_timeline\Plugin\Block;

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
    $nid = $node->id();
    $now = new DrupalDateTime('now');
    $now_timestamp = $now->getTimestamp();
    $project_start_timestamp = $node->field_project_start->date->getTimestamp();
    $project_end_timestamp = $node->field_project_finish->date->getTimestamp();
    $timeline_items = [
      [
        'title' => t('Project start'),
        'startDate' => $node->field_project_start->value,
        'endDate' => '',
        'type' => 'system',
        'state' => $project_start_timestamp < $now_timestamp ? 'passed' : 'upcomming',
        'nid' => 0,
      ],
      [
        'title' => t('Project finish'),
        'startDate' => $node->field_project_finish->value,
        'endDate' => '',
        'type' => 'system',
        'state' => $project_end_timestamp < $now_timestamp ? 'passed' : 'upcomming',
        'nid' => 0,
      ],
    ];

    $query = \Drupal::entityQuery('node');
    $query->condition('field_project_reference', $nid);
    $query->condition('type', 'hearing');
    $entity_ids = $query->execute();
    if (!empty($entity_ids)) {
      $hearings = Node::loadMultiple($entity_ids);
      foreach ($hearings as $hearing) {
        $timeline_items[] = [
          'title' => $hearing->title->value,
          'startDate' => $hearing->field_reply_deadline->value,
          'endDate' => '',
          'type' => 'hearing',
          'state' => $hearing->field_reply_deadline->date->getTimestamp() < $now_timestamp ? 'passed' : 'upcomming',
          'nid' => $hearing->nid->value,
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
