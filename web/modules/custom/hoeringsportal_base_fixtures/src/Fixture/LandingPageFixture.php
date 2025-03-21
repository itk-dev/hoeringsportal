<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\hoeringsportal_citizen_proposal_fixtures\Fixture\CitizenProposalLandingPageFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Landing page fixture.
 *
 * @package Drupal\hoeringsportal_hearing_fixtures\Fixture
 */
final class LandingPageFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $page = Node::create([
      'type' => 'landing_page',
      'title' => 'Forside-ny',
      'field_show_page_title' => FALSE,
    ]);

    $pageParagraphs = [];
    $pageParagraphLinks = [];

    $paragraphLink = Paragraph::create([
      'type' => 'link',
      'field_decorative_arrow' => TRUE,
      'field_icon' => [$this->getReference('media:building-user-solid')],
      'field_link' => [$this->getReference('node:landing_page:Proposals')],
    ]);
    $paragraphLink->save();
    $pageParagraphLinks[] = $paragraphLink;

    $paragraphLink = Paragraph::create([
      'type' => 'link',
      'field_decorative_arrow' => TRUE,
      'field_icon' => [$this->getReference('media:comments-solid')],
      'field_link' => [$this->getReference('node:landing_page:Hearings')],
    ]);
    $paragraphLink->save();
    $pageParagraphLinks[] = $paragraphLink;

    $paragraphLink = Paragraph::create([
      'type' => 'link',
      'field_decorative_arrow' => TRUE,
      'field_icon' => [$this->getReference('media:calendar-days-solid')],
      'field_link' => [$this->getReference('node:landing_page:Public meetings')],
    ]);
    $paragraphLink->save();
    $pageParagraphLinks[] = $paragraphLink;

    $paragraphLink = Paragraph::create([
      'type' => 'link',
      'field_decorative_arrow' => TRUE,
      'field_icon' => [$this->getReference('media:folder-open-solid')],
      'field_link' => [$this->getReference('node:landing_page:Projects')],
    ]);
    $paragraphLink->save();
    $pageParagraphLinks[] = $paragraphLink;

    $paragraph = Paragraph::create([
      'type' => 'links_on_a_background_image',
      'field_paragraph_image' => [$this->getReference('media:Large3')],
      'field_links_list' => array_map(
        static fn(Paragraph $paragraphLink)=>[
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

    $page->set('field_section', array_map(
      static fn(Paragraph $paragraph)=>[
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ],
      $pageParagraphs
    ));

    $page->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      MediaFixture::class,
      CitizenProposalLandingPageFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['node', 'landing_page'];
  }

}
