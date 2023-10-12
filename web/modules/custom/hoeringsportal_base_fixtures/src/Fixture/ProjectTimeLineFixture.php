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
class ProjectTimeLineFixture  extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {



  /**
   * {@inheritdoc}
   */
  public function load() {

    $node = Node::create([
      'type' => 'project',
      'title' => 'project - Projekt Tidslinje',
      'status' => NodeInterface::PUBLISHED,

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
