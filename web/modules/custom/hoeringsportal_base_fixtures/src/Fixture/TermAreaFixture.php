<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

/**
 * Area term fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class TermAreaFixture extends AbstractTaxonomyTermFixture {
  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'area';

  /**
   * {@inheritdoc}
   */
  protected static $terms = [
    'Hele kommunen',
    'Åby',
    'Åbyhøj',
    'Beder - Malling',
    'Brabrand - Gellerup',
    'Harlev - Framlev',
    'Hårup - Mejlby',
    'Hasle',
    'Hasselager - Kolt',
    'Hjortshøj',
    'Holme - Højbjerg - Skåde',
    'Lisbjerg',
    'Lystrup-Elsted-Nye',
    'Mårslet',
    'Midtbyen',
    'Sabro - Borum',
    'Skejby-Christiansbjerg',
    'Skæring - Egå',
    'Skødstrup - Løgten',
    'Solbjerg',
    'Stavtrup - Ormslev',
    'Tilst - Brabrand Nord',
    'Tranbjerg',
    'Trige - Spørring',
    'Vejlby-Risskov',
    'Viby',
  ];

}
