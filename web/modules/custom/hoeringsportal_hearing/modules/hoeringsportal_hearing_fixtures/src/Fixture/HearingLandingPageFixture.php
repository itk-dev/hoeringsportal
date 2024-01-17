<?php

namespace Drupal\hoeringsportal_hearing_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\hoeringsportal_base_fixtures\Fixture\MediaFixture;
use Drupal\hoeringsportal_base_fixtures\Fixture\ParagraphFixture;
use Drupal\node\Entity\Node;

/**
 * Landing page fixture.
 *
 * @package Drupal\hoeringsportal_hearing_fixtures\Fixture
 */
class HearingLandingPageFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    // Hearings landing page.
    $entity = Node::create([
      'type' => 'landing_page',
      'title' => 'Høringer',
      'field_teaser' => [
        'value' => <<<'BODY'
Her har du mulighed for at gøre opmærksom på dine synspunkter om en konkret høringssag.

VIGTIGT: Der kan gå længere tid inden høringssvar bliver vist på siden. Dit svar er modtaget, når du har fået en kvitteringsmail.
BODY
      ],
      'field_media_image_single' => ['target_id' => $this->getReference('media:Large2')->id()],
      'field_section' => [
        [
          'target_id' => $this->getReference('paragraph:content_list:all_hearings')->id(),
          'target_revision_id' => $this->getReference('paragraph:content_list:all_hearings')->getRevisionId(),
        ],
        [
          'target_id' => $this->getReference('paragraph:teaser_row:teaser_row1')->id(),
          'target_revision_id' => $this->getReference('paragraph:teaser_row:teaser_row1')->getRevisionId(),
        ],
      ],
    ]);
    $entity->save();
    $this->addReference('node:landing_page:Hearings', $entity);
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
    return ['node'];
  }

}
