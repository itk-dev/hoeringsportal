<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\hoeringsportal_base_fixtures\Helper\Helper;
use Drupal\hoeringsportal_hearing_fixtures\Fixture\HearingLandingPageFixture;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Page fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class MenuItemFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {
  const MENU_ITEMS = [
    'Høringer',
  ];

  /**
   * The fixtures helper service.
   *
   * @var \Drupal\hoeringsportal_base_fixtures\Helper\Helper
   */
  protected Helper $helper;

  /**
   * Constructor.
   */
  public function __construct(Helper $helper) {
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $hearingPage = $this->getReference('node:landing_page:Hearings');
    MenuLinkContent::create([
      'title' => $hearingPage->title->value,
      'link' => ['uri' => 'entity:node/' . $hearingPage->id()],
      'menu_name' => 'main',
      'expanded' => FALSE,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      HearingLandingPageFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['menu_item'];
  }

}
