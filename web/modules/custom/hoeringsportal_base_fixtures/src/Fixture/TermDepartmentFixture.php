<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

/**
 * Department term fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class TermDepartmentFixture extends AbstractTaxonomyTermFixture {
  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'department';

  /**
   * Constructor.
   */
  public function __construct() {
    if (empty(static::$terms)) {
      static::$terms = array_map(static fn ($i) => 'Department ' . $i, range(1, 3));
    }
    parent::__construct();
  }

}
