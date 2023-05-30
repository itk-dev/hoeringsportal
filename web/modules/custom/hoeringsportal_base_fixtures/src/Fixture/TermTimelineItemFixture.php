<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

/**
 * Timeline item term fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class TermTimelineItemFixture extends TaxonomyTermFixture {
  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'timeline_item_types';

  /**
   * {@inheritdoc}
   */
  protected static $terms = [
    'Debat',
    'Møde',
  ];

}
