<?php

namespace Drupal\hoeringsportal_project_timeline\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\itk_pretix\Plugin\Field\FieldType\PretixDate;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;

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
      return [];
    }

    if ($current_node->bundle() != 'project') {
      if (!empty($current_node->field_project_reference->target_id)) {
        $node = Node::load($current_node->field_project_reference->target_id);
      }
      else {
        return [];
      }
    }
    else {
      $node = $current_node;
    }

    $nid = $node->id();
    $now = new DrupalDateTime('now');
    $now_timestamp = $now->getTimestamp();

    $timeline_items = [];

    // Add start and end date to timeline items.
    if (isset($node->field_project_start->date)) {
      $project_start_timestamp = $node->field_project_start->date->getTimestamp();
      $timeline_items[] = [
        'title' => t('Start'),
        'startDate' => $node->field_project_start->date->format(\DateTimeInterface::ATOM),
        'endDate' => NULL,
        'type' => 'system',
        'description' => NULL,
        'state' => $project_start_timestamp < $now_timestamp ? 'passed' : 'upcomming',
        'nid' => 0,
        'link' => NULL,
        'color' => '#858585',
        'label' => t('Project start'),
      ];
    }

    if (isset($node->field_project_finish->date)) {
      $project_end_timestamp = $node->field_project_finish->date->getTimestamp();
      $timeline_items[] = [
        'title' => t('Expected finish'),
        'startDate' => $node->field_project_finish->date->format(\DateTimeInterface::ATOM),
        'endDate' => NULL,
        'type' => 'system',
        'description' => NULL,
        'state' => $project_end_timestamp < $now_timestamp ? 'passed' : 'upcomming',
        'nid' => 0,
        'link' => NULL,
        'color' => '#858585',
        'label' => t('Expected end date'),
      ];
    }

    // Add hearings to timeline items.
    $query = \Drupal::entityQuery('node');
    $query->accessCheck();
    $query->condition('field_project_reference', $nid);
    $query->condition('type', 'hearing');
    $entity_ids = $query->execute();
    if (!empty($entity_ids)) {
      $hearings = Node::loadMultiple($entity_ids);
      foreach ($hearings as $hearing) {
        if (isset($hearing->field_reply_deadline->date)) {
          $timeline_items[] = [
            'title' => $hearing->title->value,
            'startDate' => $hearing->field_reply_deadline->date->format(\DateTimeInterface::ATOM),
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
    $query->accessCheck();
    $query->condition('field_project_reference', $nid);
    $query->condition('type', 'public_meeting');
    $entity_ids = $query->execute();
    if (!empty($entity_ids)) {
      $meetings_nodes = Node::loadMultiple($entity_ids);
      foreach ($meetings_nodes as $meeting_node) {
        /** @var \Drupal\Core\Field\FieldItemList $meetings */
        $meetings = iterator_to_array($meeting_node->get('field_pretix_dates'));
        // Sort ascending by start time.
        usort($meetings, static function (PretixDate $a, PretixDate $b) {
          return $a->time_from <=> $b->time_from;
        });
        /** @var \Drupal\itk_pretix\Plugin\Field\FieldType\PretixDate $last_meeting */
        $last_meeting = end($meetings);
        if ($last_meeting) {
          $timeline_items[] = [
            'title' => $meeting_node->title->value,
            // Only one date is used in JS and we want it to be end date.
            'startDate' => $last_meeting->time_from->format(\DateTimeInterface::ATOM),
            'endDate' => $last_meeting->time_from->format(\DateTimeInterface::ATOM),
            'type' => 'meeting',
            'description' => NULL,
            'state' => $last_meeting->time_from < $now ? 'passed' : 'upcomming',
            'nid' => $meeting_node->nid->value,
            'link' => NULL,
            'color' => '#333333',
            'label' => t('Public meeting'),
          ];
        }
      }
    }

    // Add paragraph field values to timeline.
    foreach ($node->field_timeline_items->getValue() as $paragraph_item) {
      $paragraph_obj = Paragraph::load($paragraph_item['target_id']);
      $color = '#858585';
      $label = $node->getTitle();
      if (isset($paragraph_obj->field_timeline_taxonomy_type->target_id)) {
        $term = Term::load($paragraph_obj->field_timeline_taxonomy_type->target_id);
        if (NULL !== $term) {
          $color = $term->field_timeline_item_color->color;
          $label = $term->getName();
        }
      }

      if (isset($paragraph_obj->field_timeline_date->date)) {
        $timeline_items[] = [
          'title' => $paragraph_obj->field_timeline_title->value,
          'startDate' => $paragraph_obj->field_timeline_date->date->format(\DateTimeInterface::ATOM),
          'endDate' => isset($paragraph_obj->field_timeline_end_date->date) ? $paragraph_obj->field_timeline_end_date->date->format(\DateTimeInterface::ATOM) : NULL,
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
