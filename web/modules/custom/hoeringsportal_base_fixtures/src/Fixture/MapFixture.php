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
class MapFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {

    $node = Node::create([
      'type' => 'public_meeting',
      'title' => 'public_meeting - Heste Meeting',
      'status' => NodeInterface::PUBLISHED,
      "field_pretix_event_settings" => ['template_event' => 'testvej 1', 'synchronize_event' => FALSE],
    //  "field_map_configuration" => 'field_map_configuration',
     //: "field_map_type" => ''

          ]);
    $this->addReference('page_map:fixture-1', $node);
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
