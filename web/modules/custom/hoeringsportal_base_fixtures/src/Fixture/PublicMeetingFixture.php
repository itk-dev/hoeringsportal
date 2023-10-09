<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Page fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class PublicMeetingFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {

    $node = Node::create([
      'type' => 'public_meeting',
      'title' => 'public_meeting - Heste Meeting',
      'status' => NodeInterface::PUBLISHED,
      "field_area" => ['tid' => date("Y-m-d", 1283166912) ],
//      "field_cancelled_date" => 'field_cancelled_date'  ,
//      "field_cancelled_text" => 'field_cancelled_text'  ,
//      "field_contact" => 'field_contact'  ,
//      "field_content_state" => 'field_content_state'  ,
//      "field_description" => 'field_description'  ,
//      "field_email_address" => 'a@a.dk'  ,
////      "field_first_meeting_time" => 'field_first_meeting_time'  , datao
//      "field_hidden_signup" => 'field_hidden_signup' ,
////      "field_last_meeting_time" => 'field_last_meeting_time' ,
//      "field_last_meeting_time_end" => 'field_last_meeting_time_end' ,
//      "field_map" => 'field_map' ,
////      "field_media_document" => 'field_media_document'  ,
////      "field_media_image_single" => 'field_media_image_single' ,
////      "field_pretix_dates" => 'field_pretix_dates' ,
"field_pretix_event_settings" => ['template_event' => 'testvej 1', 'synchronize_event' => FALSE],
      "field_email" => 'parent@test.dk ',
////      "field_project_reference" => 'field_project_reference' ,
//      "field_public_meeting_cancelled" => 'field_public_meeting_cancelled',
//      "field_registration_deadline" => 'field_registration_deadline',
////      "field_section" => 'field_section' ,
//      "field_signup_link" => 'field_signup_link' ,
//      "field_signup_selection" => 'field_signup_selection' ,
//      "field_signup_text" => 'field_signup_text'  ,
//      "field_teaser" => 'field_teaser' ,
//      "field_type" => 'field_type' ,
    ]);
    $this->addReference('public_meeting:fixture-1', $node);
    $node->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['nodes'];
  }

}
