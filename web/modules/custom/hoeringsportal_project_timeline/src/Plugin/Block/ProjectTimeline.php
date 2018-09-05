<?php

namespace Drupal\hoeringsportal_project_timeline\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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

    $a = 1;

    /*
    Example array of a timelineItem

    array(
    'title' => 'testing title',
    'startDate' => '2018-06-21',
    'endDate' => '2018-08-05',
    'type' => 'hearing',
    'state' => 'passed'
    ),
     */
    $timeline_items = [
      [
        'title' => 'Project start',
        'startDate' => $node->field_project_start->value,
        'endDate' => $node->field_project_finish->value,
        'type' => 'system',
        'state' => 'passed',
      ],
      [
        'title' => t('Project finish'),
        'startDate' => $node->field_project_finish->value,
        'endDate' => $node->field_project_finish->value,
        'type' => 'system',
        'state' => 'passed',
      ],
    ];

    $timeline_items_static = [
      [
        'title' => 'testing title',
        'startDate' => '2018-06-21',
        'endDate' => '2018-08-05',
        'type' => 'hearing',
        'state' => 'passed',
      ],
      [
        'title' => 'testing extra',
        'startDate' => '2019-06-21',
        'endDate' => '2019-08-05',
        'type' => 'hearing',
        'state' => 'upcomming',
      ],
    ];

    $timeline_items = array_merge($timeline_items, $timeline_items_static);

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
