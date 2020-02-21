<?php

namespace Drupal\hoeringsportal_project_timeline\Plugin\Block;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\taxonomy\Entity\Term;
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
    $current_node = \Drupal::routeMatch()->getParameter('node');
    if (!$current_node) {
      return;
    }

    if ($current_node->bundle() != 'project') {
      if (!empty($current_node->field_project_reference->target_id)) {
        $node = Node::load($current_node->field_project_reference->target_id);
      }
      else {
        return;
      }
    }
    else {
      $node = $current_node;
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
        'description' => NULL,
        'state' => $project_start_timestamp < $now_timestamp ? 'passed' : 'upcomming',
        'nid' => 0,
        'link' => NULL,
        'color' => '#333333',
        'label' => t('Project start'),
      ],
      [
        'title' => t('Expected end date'),
        'startDate' => $node->field_project_finish->value,
        'endDate' => NULL,
        'type' => 'system',
        'description' => NULL,
        'state' => $project_end_timestamp < $now_timestamp ? 'passed' : 'upcomming',
        'nid' => 0,
        'link' => NULL,
        'color' => '#333333',
        'label' => t('Expected end date'),
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
        if (isset($hearing->field_reply_deadline->value)) {
          $timeline_items[] = [
            'title' => $hearing->title->value,
            'startDate' => $hearing->field_reply_deadline->value,
            'endDate' => NULL,
            'type' => 'hearing',
            'description' => $hearing->field_teaser->value,
            'state' => $hearing->field_reply_deadline->date->getTimestamp() < $now_timestamp ? 'passed' : 'upcomming',
            'nid' => $hearing->nid->value,
            'link' => NULL,
            'color' => '#008486',
            'label' => t('Hearing'),
          ];
        }
      }
    }

    // Add public meetings to timeline items.
    $query = \Drupal::entityQuery('node');
    $query->condition('field_project_reference', $nid);
    $query->condition('type', 'public_meeting');
    $entity_ids = $query->execute();
    if (!empty($entity_ids)) {
      $meetings_nodes = Node::loadMultiple($entity_ids);
      foreach ($meetings_nodes as $meeting_node) {
        $meetings = $meeting_node->get('field_pretix_dates')->getValue();
        usort($meetings, function ($a, $b) {
          return date('U', strtotime($a['time_from'])) - date('U', strtotime($b['time_from']));
        });
        $first_meeting = $meetings[0];
        $last_meeting = end($meetings);
        $timeline_items[] = [
          'title' => $meeting_node->title->value,
          'startDate' => date('c', strtotime($first_meeting['time_from'])),
          'endDate' => date('c', strtotime($last_meeting['time_from'])),
          'type' => 'meeting',
          'description' => NULL,
          'state' => date('U', strtotime($last_meeting['time_from'])) < $now_timestamp ? 'passed' : 'upcomming',
          'nid' => $meeting_node->nid->value,
          'link' => NULL,
          'color' => '#B2DADA',
          'label' => t('Public meeting'),
        ];
      }
    }

    // Add paragraph field values to timeline.
    foreach ($node->field_timeline_items->getValue() as $paragraph_item) {
      $paragraph_obj = Paragraph::load($paragraph_item['target_id']);
      $timeline_type_term_id = $paragraph_obj->field_timeline_taxonomy_type->target_id;
      $color = isset($timeline_type_term_id) ? Term::load($timeline_type_term_id)->field_timeline_item_color->color : '#333';
      $label = isset($timeline_type_term_id) ? Term::load($timeline_type_term_id)->getName() : $node->getTitle();
      if (isset($paragraph_obj->field_timeline_date->value)) {
        $timeline_items[] = [
          'title' => $paragraph_obj->field_timeline_title->value,
          'startDate' => $paragraph_obj->field_timeline_date->value,
          'endDate' => NULL,
          'type' => isset($label) ? str_replace(' ', '_', $label) : '',
          'description' => $paragraph_obj->field_timeline_description->value,
          'state' => $paragraph_obj->field_timeline_date->date->getTimestamp() < $now_timestamp ? 'passed' : 'upcomming',
          'nid' => NULL,
          'link' => $paragraph_obj->field_timeline_link->uri,
          'color' => $color,
          'label' => $label,
        ];
      }
    }

    /** @var \Drupal\Core\Datetime\Entity\DateFormat $date_format */
    $date_format = DateFormat::load('hoeringsportal_date');

    return [
      '#theme' => 'hoeringsportal_project_timeline',
      '#node' => $node,
      '#attached' => [
        'library' => [
          'hoeringsportal_project_timeline/project_timeline',
        ],
        'drupalSettings' => [
          'timeline' => [
            'items' => $timeline_items,
            'options' => [
              'date_format' => NULL !== $date_format ? $date_format->getPattern() : 'd/m/Y',
            ],
          ],
        ],
      ],
    ];
  }

}
