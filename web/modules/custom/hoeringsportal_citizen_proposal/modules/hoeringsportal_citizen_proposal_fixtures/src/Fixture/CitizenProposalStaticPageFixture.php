<?php

namespace Drupal\hoeringsportal_citizen_proposal_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;

/**
 * Static page fixture.
 *
 * @package Drupal\hoeringsportal_citizen_proposal_fixtures\Fixture
 */
class CitizenProposalStaticPageFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    // Citizen proposal landing page.
    $entity = Node::create([
      'type' => 'static_page',
      'title' => 'Tak for dit borgerforslag',
      'field_teaser' => [
        'value' => <<<'BODY'
Tak for dit borgerforslag.
BODY
      ],
    ]);
    $entity->save();
    $this->addReference('node:static_page:thanks', $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['node'];
  }

}
