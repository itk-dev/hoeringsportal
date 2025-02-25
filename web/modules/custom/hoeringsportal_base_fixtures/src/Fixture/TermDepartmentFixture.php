<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

/**
 * Department term fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
final class TermDepartmentFixture extends AbstractTaxonomyTermFixture {
  public const int NUMBER_OF_TERMS = 3;

  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'department';

  /**
   * Constructor.
   */
  public function __construct() {
    if (empty(static::$terms)) {
      static::$terms = array_map(static fn ($i) => sprintf('Department %d', $i), range(1, self::NUMBER_OF_TERMS));
    }
    parent::__construct();
  }

}
