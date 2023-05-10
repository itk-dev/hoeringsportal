<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

/**
 * Media library term fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class TermMediaLibraryFixture extends TaxonomyTermFixture {
  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'media_library';

  /**
   * {@inheritdoc}
   */
  protected static $terms = [
    'Billede' => [
      'MKB',
      'MSB',
      'BA',
      'MTM',
    ],

    'Fil' => [
      'MKB',
      'MSB',
      'BA',
      'MTM',
    ],
  ];

}
