<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

/**
 * Tag term fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class TermDepartmentFixture extends TaxonomyTermFixture {
  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'department';

  public function __construct() {
    if (empty(static::$terms)) {
      static::$terms = array_map(static fn ($i) => 'Department '.$i, range(1, 3));
    }
  }

}
