<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

/**
 * Pretix config fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class PretixConfigFixture extends AbstractConfigFixture {

  /**
   * {@inheritdoc}
   */
  protected static array $config = [
    'itk_pretix.pretixconfig' => [
      'pretix_url' => 'http://pretix.hoeringsportal.local.itkdev.dk/',
      'organizer_slug' => 'hoeringsportal',
      'api_token' => 'v84pb9f19gv5gkn2d8vbxoih6egx2v00hpbcwzwzqoqqixt22locej5rffmou78e',
      'template_event_slugs' => 'template-series',
      'langcode' => 'da',
      'event_exporters_message' => 'Be careful …',
      'event_exporters_enabled' => [
        'checkinlist' => 'checkinlist',
        'checkinlistpdf' => 'checkinlistpdf',
      ],
    ],
  ];

}
