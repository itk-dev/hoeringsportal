<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

/**
 * Tag term fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class TermTagFixture extends AbstractTaxonomyTermFixture {
  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'tags';

  /**
   * {@inheritdoc}
   */
  protected static $terms = [
    'Dagtilbud og skole',
    'Kommune- og lokalplanlægning',
    'Kultur og borgerservice',
    'Miljø og energi',
    'Sociale forhold og beskæftigelse ',
    'Sundhed og omsorg',
    'Trafik og mobilitet',
  ];

}
