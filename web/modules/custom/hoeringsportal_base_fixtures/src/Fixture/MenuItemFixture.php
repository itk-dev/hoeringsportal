<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\hoeringsportal_citizen_proposal_fixtures\Fixture\CitizenProposalLandingPageFixture;
use Drupal\hoeringsportal_hearing_fixtures\Fixture\HearingLandingPageFixture;
use Drupal\hoeringsportal_project_fixtures\Fixture\ProjectLandingPageFixture;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Page fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class MenuItemFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function load() {
    // Main menu
    foreach ([
      'node:landing_page:Hearings',
      'node:landing_page:Proposals',
      'node:landing_page:Public meetings',
      'node:landing_page:Projects',
    ] as $weight => $name) {
      $page = $this->getReference($name);
      MenuLinkContent::create([
        'title' => $page->title->value,
        'link' => ['uri' => 'entity:node/' . $page->id()],
        'menu_name' => 'main',
        'expanded' => FALSE,
        'weight' => $weight,
      ])->save();
    }
    // Secondary navigation
    foreach ([
      'node:static_page:About',
    ] as $weight => $name) {
      $page = $this->getReference($name);
      MenuLinkContent::create([
        'title' => $page->title->value,
        'link' => ['uri' => 'entity:node/' . $page->id()],
        'menu_name' => 'secondary-navigation',
        'expanded' => FALSE,
        'weight' => $weight,
      ])->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      HearingLandingPageFixture::class,
      CitizenProposalLandingPageFixture::class,
      ProjectLandingPageFixture::class,
      PublicMeetingFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['menu_item'];
  }

}
