<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\hoeringsportal_hearing_fixtures\Fixture\HearingLandingPageFixture;
use Drupal\hoeringsportal_citizen_proposal_fixtures\Fixture\CitizenProposalLandingPageFixture;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Page fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class MenuItemFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function load() {
    $hearingPage = $this->getReference('node:landing_page:Hearings');
    MenuLinkContent::create([
      'title' => $hearingPage->title->value,
      'link' => ['uri' => 'entity:node/' . $hearingPage->id()],
      'menu_name' => 'main',
      'expanded' => FALSE,
      'weight' => 0,
    ])->save();

    $citizenProposalPage = $this->getReference('node:landing_page:Proposals');
    MenuLinkContent::create([
      'title' => $citizenProposalPage->title->value,
      'link' => ['uri' => 'entity:node/' . $citizenProposalPage->id()],
      'menu_name' => 'main',
      'expanded' => FALSE,
      'weight' => 3,
    ])->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      HearingLandingPageFixture::class,
      CitizenProposalLandingPageFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['menu_item'];
  }

}
