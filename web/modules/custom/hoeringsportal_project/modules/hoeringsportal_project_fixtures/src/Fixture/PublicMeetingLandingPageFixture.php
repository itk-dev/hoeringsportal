<?php

namespace Drupal\hoeringsportal_project_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\hoeringsportal_base_fixtures\Fixture\MediaFixture;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Landing page fixture.
 *
 * @package Drupal\hoeringsportal_hearing_fixtures\Fixture
 */
class PublicMeetingLandingPageFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity = Node::create([
      'type' => 'landing_page',
      'title' => 'Begivenheder',
      'field_teaser' => [
        'value' => <<<'BODY'
<p>Samler information om begivenheder i kommunen.</p>
BODY
      ],
      'field_media_image_single' => [$this->getReference('media:Large3')],
    ]);

    $paragraph = Paragraph::create([
      'type' => 'content_list',
      'field_content_list' => [
        'target_id' => 'all_meetings',
        'display_id' => 'default',
        'data' => '',
      ],
      'field_list_title' => 'All public meetings',
    ]);
    $paragraph->save();

    $entity->set('field_section', [
      [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ],
    ]);

    $entity->save();
    $this->addReference('node:landing_page:Public meetings', $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      MediaFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['node', 'project'];
  }

}
