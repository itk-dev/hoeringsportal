<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Page fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class PublicMeetingFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {

    $node = Node::create([
      'type' => 'public_meeting',
      'title' => 'public_meeting - Heste Meeting',
      'status' => NodeInterface::PUBLISHED,
      'field_area' => ['tid' => date('Y-m-d', 2222222322222)],
      'field_cancelled_date' => date('Y-m-d', 1283166912),
      'field_cancelled_text' => 'field_cancelled_text'  ,
      'field_contact' => 'field_contact'  ,
      'field_content_state' => 'active'  ,
      'field_description' => 'field_description'  ,
      'field_email_address' => 'a@a.dk'  ,
      'field_first_meeting_time' => date('Y-m-d', 1283166912)  ,
      'field_hidden_signup' => 4 ,
      'field_last_meeting_time' => date('Y-m-d', 1283166912) ,
      'field_last_meeting_time_end' => date('Y-m-d', 2222222322222),
      'field_media_document'  => ['target_id' => $this->getReference('media_library:Fil:MTM')->id()],
      'field_media_image_single' => ['target_id' => $this->getReference('media_library:Billede:MTM')->id()] ,
      'field_pretix_event_settings' =>
        ['template_event' => 'testvej 1', 'synchronize_event' => FALSE],
      'field_email' => 'parent@test.dk ',
      'field_project_reference' => 'field_project_reference' ,
      'field_registration_deadline' => ['value' => 'word'],
      'field_section' =>
        [
          'target_id' => $this->getReference('paragraph:content_list:all_public_meetings')->id(),
          'target_revision_id' => $this->getReference('paragraph:content_list:all_public_meetings')->getRevisionId(),
        ],
      'field_signup_selection' => 'field_signup_selection' ,
      'field_signup_text' => 'field_signup_text'  ,
      'field_teaser' => 'field_teaser' ,
    ]);
    $this->addReference('public_meeting:fixture-1', $node);
    $node->save();
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
    return ['nodes'];
  }

}
