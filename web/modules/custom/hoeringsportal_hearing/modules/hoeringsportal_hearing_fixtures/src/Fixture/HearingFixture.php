<?php

namespace Drupal\hoeringsportal_hearing_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\hoeringsportal_base_fixtures\Fixture\MediaFixture;
use Drupal\hoeringsportal_base_fixtures\Fixture\ParagraphFixture;
use Drupal\node\Entity\Node;

/**
 * Page fixture.
 *
 * @package Drupal\hoeringsportal_hearing_fixtures\Fixture
 */
class HearingFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    // Hearing node.
    $entity = Node::create([
      'type' => 'hearing',
      'title' => 'Høring1',
      'field_description' => [
        'value' => '
Lorem ipsum bum bum bum.

Dette er en høring. Den har ID node:landing_page:Hearing1 ',
        'format' => 'filtered_html',
      ],
      'field_media_image' => [
        ['target_id' => $this->getReference('media:Medium1')->id()],
        ['target_id' => $this->getReference('media:Medium2')->id()],
        ['target_id' => $this->getReference('media:Map1')->id()],
      ],
      'field_content_state' => 'upcoming',
      'field_media_document' => [
        ['target_id' => $this->getReference('media:Document1')->id()],
        ['target_id' => $this->getReference('media:Pdf1')->id()],
      ],
      'field_start_date' => DrupalDateTime::createFromFormat('U', strtotime('tomorrow'))->format('Y-m-d\TH:i:s'),
      'field_reply_deadline' => DrupalDateTime::createFromFormat('U', strtotime('tomorrow'))->format('Y-m-d\TH:i:s'),
      'field_edoc_casefile_id' => '',
      'field_tags' => ['target_id' => $this->getReference('tags:Kultur og borgerservice')->id()],
      'field_getorganized_case_id' => '',
      'field_contact'  => [
        'value' => '
This is the contact field.

Input address here.',
        'format' => 'filtered_html',
      ],
      'field_map' => '',
      'field_map_display' => '',
      'field_lokalplaner' => '',
      'field_area' => ['target_id' => $this->getReference('area:Hele kommunen')->id()],
      'field_project_reference' => '',
      'field_deskpro_agent_email' => '',
      'field_teaser' => 'Lorem ipsum teaser',
      'field_hearing_ticket_add' => '',
      'field_type' => ['target_id' => $this->getReference('hearing_type:Kommuneplan:Temaplan detailhandel')->id()],
      'field_video_embed' => '',
      'field_more_info' => [
        'value' => '
This is the more info field.

Lorem ipsum 1234 Lorem ipsum',
        'format' => 'filtered_html',
      ],
      'field_hearing_ticket_list' => '',
    ]);
    $entity->save();
    $this->addReference('node:hearing:Hearing1', $entity);
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
