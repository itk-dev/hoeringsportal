<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

/**
 * Type term fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class TermTypeFixture extends AbstractTaxonomyTermFixture {
  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'type';

  /**
   * {@inheritdoc}
   */
  protected static $terms = [
    'Beskæftigelse',
    'Børn',
    'Dagtilbud og skole',
    'Fritid',
    'Indkaldelse af ideer og forslag',
    'Klima',
    'Kommuneplan',
    'Kultur',
    'Lokalplan',
    'Sundhed og trivsel',
    'Trafik og transport',
    'Uddannelse',
    'Ældre',
  ];

}
