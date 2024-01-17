<?php

namespace Drupal\hoeringsportal_hearing_fixtures\Fixture;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
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
    foreach (range(1, 3) as $i) {
      // Hearing node.
      $entity = Node::create([
        'type' => 'hearing',
        'title' => 'Høring ' . $i,
        'field_description' => [
          'value' => '
Lorem ipsum bum bum bum.

Dette er en høring. Den har ID node:landing_page:Hearing1 ',
          'format' => 'hearing_description',
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
        'field_start_date' => DrupalDateTime::createFromFormat('U',
          strtotime('tomorrow'))->format('Y-m-d\TH:i:s'),
        'field_reply_deadline' => DrupalDateTime::createFromFormat('U',
          strtotime('tomorrow'))->format('Y-m-d\TH:i:s'),
        'field_edoc_casefile_id' => '',
        'field_tags' => [
          'target_id' => $this->getReference('tags:Kultur og borgerservice')
            ->id(),
        ],
        'field_getorganized_case_id' => 'test-case-id',
        'field_contact' => [
          'value' => '
This is the contact field.

Input address here.',
          'format' => 'filtered_html',
        ],
        'field_map' => [
          'type' => 'point',
          'data' => '{"type":"Feature","properties":[],"geometry":{"type":"Point","coordinates":[10.213748135074276,56.15345564612786]}}',
          'geojson' => '',
          'localplanids' => '',
          'point' => '{"type":"FeatureCollection","crs":{"type":"name","properties":{"name":"urn:ogc:def:crs:EPSG::4326"}},"features":[{"type":"Feature","properties":{},"geometry":{"type":"Point","coordinates":[10.213748135074276,56.15345564612786]}}]}',
        ],
        'field_map_display' => '',
        'field_lokalplaner' => '',
        'field_area' => [
          'target_id' => $this->getReference('area:Hele kommunen')
            ->id(),
        ],
        'field_project_reference' => '',
        // If we're lucky this Deskpro data makes sense.
        'field_deskpro_department_id' => 12,
        'field_deskpro_agent_email' => 'deskpro@example.com',
        'field_teaser' => 'Lorem ipsum teaser',
        'field_hearing_ticket_add' => '',
        'field_type' => [
          'target_id' => $this->getReference('type:Kommuneplan')
            ->id(),
        ],
        'field_video_embed' => '',
        'field_more_info' => [
          'value' => '
This is the more info field.

Lorem ipsum 1234 Lorem ipsum',
          'format' => 'filtered_html',
        ],
        'field_hearing_ticket_list' => '',
        'field_department' => [
          $this->getReference('department:Department ' . $i)->id(),
        ],
      ]);
      $entity->save();
      $this->addReference('node:hearing:Hearing' . $i, $entity);
    }

    $entity = $entity->createDuplicate();
    $entity->setCreatedTime((new \DateTimeImmutable('-87 days'))->getTimestamp());
    $entity->setChangedTime((new \DateTimeImmutable('-42 days'))->getTimestamp());
    $entity->setTitle('Høring med GIS-kort');
    $entity->set('field_description', [
      'value' => <<<'EOD'
<h2>Et GIS-kort</h2>

<p>[gis:minimap:409c1dc9-b604-4f1e-9df9-2768d050acb4]</p>


<h2>Et lavt GIS-kort</h2>
<p>[gis:minimap:409c1dc9-b604-4f1e-9df9-2768d050acb4:height=200px]</p>

<h2>Et ugyldigt kort</h2>

<p>[gis:minimap:hest]</p>
EOD,
      'format' => 'hearing_description',
    ]);
    $entity->save();

    $entity = $entity->createDuplicate();
    $entity->setCreatedTime((new \DateTimeImmutable('-2 days'))->getTimestamp());
    $entity->setChangedTime((new \DateTimeImmutable('-1 days'))->getTimestamp());
    $entity->setTitle('Høring med slettede høringssvar');
    $entity->set('field_delete_date', (new DrupalDateTime('2001-01-01'))->format(DateTimeItemInterface::DATE_STORAGE_FORMAT));
    $entity->save();
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
