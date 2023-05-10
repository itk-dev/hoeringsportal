<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Page fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class ParagraphFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $paragraph = Paragraph::create([
      'type' => 'image',
      'field_paragraph_image' => [
        ['target_id' => $this->getReference('media:Large1')->id()],
      ],
    ]);
    $paragraph->save();

    $paragraph = Paragraph::create([
      'type' => 'content_block',
      'field_paragraph_image' => [
        ['target_id' => $this->getReference('media:Large3')->id()],
      ],
      'field_content_block_text' => [
        'value' => <<<'BODY'
Indholdsblock hurra hurra lorem ipsum 123.
BODY,
        'format' => 'filtered_html',
      ],
      'field_paragraph_title' => 'Indholdsblok1',
    ]);
    $paragraph->save();

    $paragraph = Paragraph::create([
      'type' => 'content_list',
      'field_content_list' => '',
      'field_list_title' => 'Indholdsblok1',
    ]);
    $paragraph->save();

    $paragraph = Paragraph::create([
      'type' => 'info_box',
      'field_content_block_text' => [
        'value' => <<<'BODY'
Info box hurra hurra lorem ipsum 123.
BODY,
        'format' => 'filtered_html',
      ],
      'field_paragraph_title' => 'Dette er en info box',
    ]);
    $paragraph->save();

    $paragraph = Paragraph::create([
      'type' => 'projekt_billede_galleri',
      'field_image_gallery'  => [
        ['target_id' => $this->getReference('media:Large1')->id()],
        ['target_id' => $this->getReference('media:Large2')->id()],
        ['target_id' => $this->getReference('media:Large3')->id()],
      ],
      'field_external_link' => [
        'uri' => 'https://example.com',
        'title' => 'Visit the website',
      ],
    ]);
    $paragraph->save();

    $paragraph = Paragraph::create([
      'type' => 'introduction',
      'field_intro_body' => [
        'value' => <<<'BODY'
Introduktion testing a paragraph.
BODY
      ],
      'field_intro_link' => [
        'uri' => 'https://example.com',
        'title' => 'Visit the website',
      ],
      'field_paragraph_title' => 'En introduktion er her',
    ]);
    $paragraph->save();

    $paragraph = Paragraph::create([
      'type' => 'teaser_row',
      'field_project_hearing_1' => '',
      'field_project_hearing_2' => '',
      'field_project_hearing_3' => '',
      'field_static_pages' => '',
      'field_paragraph_title' => 'This is a row of teasers',
      'field_type' => '',
    ]);
    $paragraph->save();

    $paragraph = Paragraph::create([
      'type' => 'text',
      'field_content_block_text' => [
        'value' => <<<'BODY'
Info box hurra hurra lorem ipsum 123.
BODY,
        'format' => 'filtered_html',
      ],
      'field_external_link' => [
        'uri' => 'https://example.com',
        'title' => 'Visit the website',
      ],
    ]);
    $paragraph->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      MediaFixture::class,
      TermTimelineItemFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['paragraph'];
  }

}
