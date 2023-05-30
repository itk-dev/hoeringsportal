<?php

namespace Drupal\hoeringsportal_citizen_proposal_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\hoeringsportal_base_fixtures\Fixture\MediaFixture;
use Drupal\hoeringsportal_base_fixtures\Fixture\ParagraphFixture;
use Drupal\node\Entity\Node;

/**
 * Landing page fixture.
 *
 * @package Drupal\hoeringsportal_citizen_proposal_fixtures\Fixture
 */
class CitizenProposalLandingPageFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    // Citizen proposal landing page.
    $entity = Node::create([
      'type' => 'landing_page',
      'title' => 'Borgerforslag',
      'field_teaser' => [
        'value' => <<<'BODY'
Her kan du oprette og støtte borgerforslag. Har du et forslag til kommunen, kan du få dit forslag publiceret her.
BODY
      ],
      'field_media_image_single' => ['target_id' => $this->getReference('media:Large2')->id()],
      'field_section' => [],
    ]);
    $entity->save();
    $this->addReference('node:landing_page:Proposals', $entity);
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
