<?php

namespace Drupal\hoeringsportal_project_fixtures\Fixture;

use Drupal\hoeringsportal_base_fixtures\Fixture\AbstractTaxonomyTermFixture;

/**
 * Project category fixture.
 *
 * @package Drupal\hoeringsportal_project_fixtures\Fixture
 */
class ProjectCategoryFixture extends AbstractTaxonomyTermFixture {

  /**
   * {@inheritdoc}
   */
  protected static $vocabularyId = 'project_categories';

  /**
   * {@inheritdoc}
   */
  protected static $terms = [
    'Byudvikling',
    'Offentlig transport',
  ];

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return array_merge(parent::getGroups(), ['project']);
  }

}
