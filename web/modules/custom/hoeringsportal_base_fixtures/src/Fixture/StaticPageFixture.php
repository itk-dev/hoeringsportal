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
class StaticPageFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $node = Node::create([
      'type' => 'static_page',
      'title' => 'Statisk side 223232323',
      'status' => NodeInterface::PUBLISHED,
      'field_media_image_single' => ['target_id' => $this->getReference('media_library:Billede:MTM')->id()],
      'field_teaser' => 'field teaser',
      'field_sidebar' => ['value' => 'Sidebar ?'],
      'field_teaser_color' => '#fff',
    ]);
    $this->addReference('static_page:fixture-1', $node);
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
