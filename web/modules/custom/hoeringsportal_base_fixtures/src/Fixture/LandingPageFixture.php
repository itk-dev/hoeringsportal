<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\hoeringsportal_citizen_proposal_fixtures\Fixture\CitizenProposalLandingPageFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\itk_admin\State\BaseConfig;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Landing page fixture.
 *
 * @package Drupal\hoeringsportal_hearing_fixtures\Fixture
 */
final class LandingPageFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * Constructor.
   */
  public function __construct(private readonly BaseConfig $baseConfig) {}

  /**
   * {@inheritdoc}
   */
  public function load() {
    $page = Node::create([
      'type' => 'landing_page',
      'title' => 'Forside',
      'field_show_page_title' => FALSE,
    ]);

    $pageParagraphs = [];
    $pageParagraphLinks = [];

    $paragraphLink = Paragraph::create([
      'type' => 'link',
      'field_decorative_arrow' => TRUE,
      'field_icon' => [$this->getReference('media:building-user-solid')],
      'field_link' => [$this->getReference('node:landing_page:Proposals')],
      'field_button_variant' => 'white',
    ]);
    $paragraphLink->save();
    $pageParagraphLinks[] = $paragraphLink;

    $paragraphLink = Paragraph::create([
      'type' => 'link',
      'field_decorative_arrow' => TRUE,
      'field_icon' => [$this->getReference('media:comments-solid')],
      'field_link' => [$this->getReference('node:landing_page:Hearings')],
      'field_button_variant' => 'white',
    ]);
    $paragraphLink->save();
    $pageParagraphLinks[] = $paragraphLink;

    $paragraphLink = Paragraph::create([
      'type' => 'link',
      'field_decorative_arrow' => TRUE,
      'field_icon' => [$this->getReference('media:calendar-days-solid')],
      'field_link' => [$this->getReference('node:landing_page:Public meetings')],
      'field_button_variant' => 'white',
    ]);
    $paragraphLink->save();
    $pageParagraphLinks[] = $paragraphLink;

    $paragraphLink = Paragraph::create([
      'type' => 'link',
      'field_decorative_arrow' => TRUE,
      'field_icon' => [$this->getReference('media:folder-open-solid')],
      'field_link' => [$this->getReference('node:landing_page:Projects')],
      'field_button_variant' => 'white',
    ]);
    $paragraphLink->save();
    $pageParagraphLinks[] = $paragraphLink;

    $paragraph = Paragraph::create([
      'type' => 'links_on_a_background_image',
      'field_paragraph_image' => [$this->getReference('media:Large3')],
      'field_links_list' => array_map(
        static fn(Paragraph $paragraphLink) => [
          'target_id' => $paragraphLink->id(),
          'target_revision_id' => $paragraphLink->getRevisionId(),
        ],
        $pageParagraphLinks
      ),
    ]);
    $paragraph->save();
    $pageParagraphs[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'content_list',
      'field_list_title' => 'Igangværende høringer',
      'field_content_list' => [
        'target_id' => 'latest_hearings',
        'display_id' => 'default',
      ],
    ]);
    $paragraph->save();
    $pageParagraphs[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'content_list',
      'field_list_title' => 'Nyeste borgerforslag',
      'field_content_list' => [
        'target_id' => 'latest_citizen_proposals',
        'display_id' => 'default',
      ],
    ]);
    $paragraph->save();
    $pageParagraphs[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'content_list',
      'field_list_title' => 'Kommende begivenheder',
      'field_content_list' => [
        'target_id' => 'latest_public_meetings',
        'display_id' => 'default',
      ],
    ]);
    $paragraph->save();
    $pageParagraphs[] = $paragraph;

    $paragraph = Paragraph::create([
      'type' => 'content_list',
      'field_list_title' => 'Seneste projekter',
      'field_content_list' => [
        'target_id' => 'latest_projects',
        'display_id' => 'default',
      ],
    ]);
    $paragraph->save();
    $pageParagraphs[] = $paragraph;

    $paragraphButtonLinks = [];
    $paragraphButtonLink = Paragraph::create([
      'type' => 'link',
      'field_decorative_arrow' => FALSE,
      'field_link' => [$this->getReference('node:static_page:About')],
      'field_button_variant' => 'petroleum',
    ]);
    $paragraphButtonLink->save();
    $paragraphButtonLinks[] = $paragraphButtonLink;

    $paragraph = Paragraph::create([
      'type' => 'content_promotion',
      'field_paragraph_image' => [$this->getReference('media:Large3')],
      'field_lead' => 'Om Deltag Aarhus',
      'field_title' => 'Hvad ved du om deltag.aarhus.dk',
      'field_abstract' => 'Se her hvad du kan bruge deltag.aarhus.dk til som borger i Aarhus Kommune',
      'field_button' => array_map(
        static fn(Paragraph $paragraphButtonLink) => [
          'target_id' => $paragraphButtonLink->id(),
          'target_revision_id' => $paragraphButtonLink->getRevisionId(),
        ],
        $paragraphButtonLinks
      ),
    ]);
    $paragraph->save();
    $pageParagraphs[] = $paragraph;

    // Add list with static pages.
    $paragraph = Paragraph::create([
      'type' => 'teaser_row',
      'field_paragraph_title' => 'Statiske sider',
      'field_content' => [$this->getReference('node:static_page:About')],
    ]);
    $paragraph->save();
    $pageParagraphs[] = $paragraph;

    $page->set(
      'field_section',
      array_map(
        static fn(Paragraph $paragraph) => [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ],
        $pageParagraphs
      )
    );

    $page->save();

    // Set our page as site front page
    // (cf. /admin/site-setup/general > “Pages” > “Front page”)
    // See \Drupal\hoeringsportal_config_settings\Form\ItkGeneralSettingsForm.
    $this->baseConfig->set('frontpage_id', $page->id());

    // Set default image for citizen proposal teaser
    // (cf. /admin/site-setup/general
    // > “Default images” > “Citizen proposal default teaser image”)
    // See \Drupal\hoeringsportal_config_settings\Form\ItkGeneralSettingsForm.
    $this->baseConfig->set(
      'citizen_proposal_teaser_default_image',
      $this->getReference('media:citizen_proposal_default_image')->id()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [MediaFixture::class, CitizenProposalLandingPageFixture::class];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['node', 'landing_page'];
  }

}
