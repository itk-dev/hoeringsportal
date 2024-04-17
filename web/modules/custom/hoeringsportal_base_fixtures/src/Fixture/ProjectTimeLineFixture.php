<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Page fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class ProjectTimeLineFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $today = strtotime('today');
    $tenDaysLater = strtotime('+10 days', $today);
    $node = Node::create([
      'type' => 'project',
      'title' => 'project - Projekt Tidslinje',
      'status' => NodeInterface::PUBLISHED,
      "field_description" => "field_description - Her forklare jeg noget",
      "field_project_finish" => date('d-m-Y', $tenDaysLater),
      "field_project_start" => date('d-m-Y', $today),
    ]);
    $this->addReference('project:fixture-1', $node);
    $node->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      MediaFixture::class,
      ParagraphFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['nodes'];
  }

}
