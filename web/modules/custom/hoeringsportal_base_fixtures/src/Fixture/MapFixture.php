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
class MapFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {

    $field_map_configuration_data = [
      'map' => [
        'maxZoomLevel' => 0,
        'minZoomLevel' => 20,
        'view' => [
          'zoomLevel' => 5,
          'x' => 659519,
          'y' => 6151191,
        ],
        'layer' => [
          [
            'namedlayer' => '#dk_standard',
          ],
          [
            'features' => TRUE,
            'features_host' => '../data/boernehaver.json',
            'features_dataType' => 'json',
            'features_type' => 'Point',
            'features_style' => [
              'namedstyle' => '#011',
            ],
            'template_info' => "<div class='widget-hoverbox-title'>{{navn}}</div><div class=\"widget-hoverbox-sub\"><% if (navn === 'Landsbybørnehaven') { %><div><%= navn %></div><% } %><div>{{Adresse}}, {{Postnr}} {{By}}</div><div><a target=\"blank\" href=\"{{SE_MERE}}\">Hjemmeside</a></div><% if (navn === 'Regnbuen') { %><div>Dette er en meget lang tekst hvor der kan stå en masse, men det er ikke sikkert at der er plads til det på kortet</div><% } %></div>",
            'name' => 'Børnehaver',
            'type' => 'geojson',
          ],
        ],
        'controls' => [
          [
            'info' => [
              'disable' => FALSE,
              'eventtype' => 'click',
              'multifeature' => 10,
              'type' => 'cloud',
            ],
            'overlay' => [
              'disable' => FALSE,
            ],
          ],
        ],
      ],
    ];

    $node = Node::create([
      'type' => 'page_map',
      'title' => 'page_map - Heste Kortet',
      'status' => NodeInterface::PUBLISHED,
      'field_pretix_event_settings' =>
        [
          'template_event' => 'testvej 2',
          'synchronize_event' => FALSE,
        ],
      'field_map_type' => [],
      'field_map_configuration' => json_encode($field_map_configuration_data),
    ]);
    $this->addReference('page_map:fixture-1', $node);
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
