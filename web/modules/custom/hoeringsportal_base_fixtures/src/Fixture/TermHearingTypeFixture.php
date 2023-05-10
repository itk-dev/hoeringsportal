<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

/**
 * Hearing type term fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class TermHearingTypeFixture extends TaxonomyTermFixture {
  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'hearing_type';

  /**
   * {@inheritdoc}
   */
  protected static $terms = [
    'Borgmesterens Afdeling' => [],
    'Borgmesterens Afdeling, Erhverv og Bæredygtig Udvikling' => [
      'Kommuneplanlægning',
    ],
    'Kommuneplan' => [
      'Temaplan detailhandel',
      'Temaplan',
      'Tillæg til kommuneplanen',
      'Idéindsamling og debatoplæg',
    ],
    'Plan afd' => [
      'Indkaldelse af idéer og forslag',
      'Kommuneplantillæg',
      'Lokalplaner',
    ],
  ];

}