<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Page fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
final class PublicMeetingFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {

    $node = Node::create([
      'type' => 'public_meeting',
      'title' => 'Public meeting with manual signup',
      'status' => NodeInterface::PUBLISHED,
      'field_teaser' => 'A public meeting',
      'field_description' => 'Description of the public meeting.',
      'field_area' => [
        $this->getReference('area:Hele kommunen'),
      ],
      'field_type' => [
        $this->getReference('type:Klima'),
      ],
      'field_contact' => 'Contact info',
      'field_content_state' => 'active',
      'field_email_address' => 'a@a.dk',
      'field_first_meeting_time' => date('Y-m-d', 1283166912),
      'field_media_document'  => [[$this->getReference('media_library:Fil:MTM')]],
      'field_media_image_single' => [
          ['target_id' => $this->getReference('media:Large1')->id()],
      ],
      'field_pretix_event_settings' => [
        'template_event' => 'template-series',
        'synchronize_event' => TRUE,
      ],
      'field_email' => 'parent@test.dk ',
      'field_signup_selection' => 'manual',
      'field_signup_text' => 'field_signup_text',
      'field_signup_link' => [
        'uri' => 'https://example.com/sign-up/',
        'title' => 'Sign up for public meeting',
      ],
      'field_registration_deadline' => (new \DateTimeImmutable('2025-01-01T18:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'field_last_meeting_time' => (new \DateTimeImmutable('2025-01-01T19:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'field_last_meeting_time_end' => (new \DateTimeImmutable('2025-01-01T21:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),

      'field_map' => [
        'type' => 'point',
        'data' => '{"type":"Feature","properties":[],"geometry":{"type":"Point","coordinates":[11.704067337123801,56.19625173058858]}}',
        'point' => '{"type":"FeatureCollection","crs":{"type":"name","properties":{"name":"urn:ogc:def:crs:EPSG::4326"}},"features":[{"type":"Feature","properties":{},"geometry":{"type":"Point","coordinates":[11.704067337123801,56.19625173058858]}}]}',
      ],

      'field_department' => [
        $this->getReference('department:Department 1')->id(),
        $this->getReference('department:Department 2')->id(),
      ],
    ]);
    $this->addReference('public_meeting:fixture-1', $node);
    $node->save();

    $node = $node->createDuplicate();
    $node->setTitle('Public meeting with pretix signup');
    $node->set('field_signup_selection', 'pretix');
    $node->set('field_pretix_dates', [
      [
        'location' => 'The location',
        'address' => 'Hack Kampmanns Plads 2, 8000 Aarhus C',
        'registration_deadline_value' => (new \DateTimeImmutable('2025-01-01T18:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_from_value' => (new \DateTimeImmutable('2025-01-01T19:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_to_value' => (new \DateTimeImmutable('2025-01-01T21:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'spots' => 87,
      ],
    ]);
    $node->set('field_map', [
      'type' => 'point',
      'data' => '{"type":"Feature","properties":[],"geometry":{"type":"Point","coordinates":[11.704067337123801,56.19625173058858]}}',
      'point' => '{"type":"FeatureCollection","crs":{"type":"name","properties":{"name":"urn:ogc:def:crs:EPSG::4326"}},"features":[{"type":"Feature","properties":{},"geometry":{"type":"Point","coordinates":[11.704067337123801,56.19625173058858]}}]}',
    ]);
    $node->set('field_pretix_event_settings', [
      // Cf. PretixConfigFixture.
      'template_event' => 'template-series',
      'synchronize_event' => TRUE,
    ]);
    $this->addReference('public_meeting:fixture-2', $node);
    $node->save();

    $node = $node->createDuplicate();
    $node->setTitle('Public meeting with pretix signup and multiple dates');
    $node->set('field_pretix_dates', [
      [
        'location' => 'The location',
        'address' => 'Hack Kampmanns Plads 2, 8000 Aarhus C',
        'registration_deadline_value' => (new \DateTimeImmutable('2024-12-31T00:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_from_value' => (new \DateTimeImmutable('2025-01-01T19:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_to_value' => (new \DateTimeImmutable('2025-01-01T21:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'spots' => 87,
      ],
      [
        'location' => 'Another location',
        'address' => 'Rådhuspladsen 1, 8000 Aarhus C',
        'registration_deadline_value' => (new \DateTimeImmutable('2025-11-30T00:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_from_value' => (new \DateTimeImmutable('2025-12-01T15:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_to_value' => (new \DateTimeImmutable('2025-12-01T16:30:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'spots' => 42,
      ],
      [
        'location' => 'The location',
        'address' => 'Hack Kampmanns Plads 2, 8000 Aarhus C',
        'registration_deadline_value' => (new \DateTimeImmutable('2025-11-30T00:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_from_value' => (new \DateTimeImmutable('2025-12-02T15:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_to_value' => (new \DateTimeImmutable('2025-12-02T16:30:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'spots' => 87,
      ],
    ]);
    $node->set('field_department', [
      $this->getReference('department:Department 1')->id(),
    ]);
    $this->addReference('public_meeting:fixture-3', $node);
    $node->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      MediaFixture::class,
      ParagraphFixture::class,
      TermAreaFixture::class,
      TermDepartmentFixture::class,
      TermTypeFixture::class,
      PretixConfigFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['nodes'];
  }

}
