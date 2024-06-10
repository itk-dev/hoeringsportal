<?php

namespace Drupal\migrate_subject_data\Commands;

use Drupal\node\Entity\Node;
use Drush\Commands\DrushCommands;

/**
 * Migrate data.
 */
class MigrateSubjectDataCommands extends DrushCommands {

  /**
   * Migrates data from taxonomy to node.
   *
   * @command migrate_subject_data:migrate
   * @aliases migrate-subject-data
   */
  public function migrate() {
    if (!\Drupal::state()->get('subject_data_migrated')) {
      $terms = $this->getTerms('hearing_type');

      $nids = \Drupal::entityQuery('node')
        ->accessCheck()
        ->condition('type', 'hearing')
        ->execute();

      // We do multiple loads because there aren't too many nodes yet.
      $nodes = Node::loadMultiple($nids);
      foreach ($nodes as $node) {
        if (!empty($node->field_hearing_type->target_id)) {
          $node->field_contact->value = $terms[$node->field_hearing_type->target_id]['contact'];
          $node->field_contact->format = 'filtered_html';
          $node->field_more_info->value = $terms[$node->field_hearing_type->target_id]['more_info'];
          $node->field_more_info->format = 'filtered_html';
          $node->save();
          $this->output()->writeln(print_r($node->field_hearing_type->target_id));
        }
      }

      \Drupal::state()->set('subject_data_migrated', 'migrated');
      $this->output()->writeln('Subject data successfully migrated.');
    }
    else {
      $this->output()->writeln('Data has already been migrated.');
    }
  }

  /**
   * Get all terms of a taxonomy.
   *
   * @param int $vid
   *   The taxonomy bundle type.
   *
   * @return array
   *   An array of terms and fields.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getTerms($vid) {
    $termData = [];
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {
      $term_obj = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term->tid);
      $termData[$term->tid] = [
        'id' => $term->tid,
        'contact' => $term_obj->field_contact->value,
        'more_info' => $term_obj->field_more_info->value,
      ];
    }

    return $termData;
  }

}
