<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\Site\Settings;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\itk_pretix\Pretix\EventHelper as PretixEventHelper;
use Drupal\itk_pretix\Pretix\OrderHelper as PretixOrderHelper;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Page fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
final class PublicMeetingFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  public function __construct(
    private readonly AccountSwitcherInterface $accountSwitcher,
    private readonly PretixEventHelper $pretixEventHelper,
    private readonly PretixOrderHelper $pretixOrderHelper,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    // Authenticate to make sure that hoeringsportal_public_meeting_node_presave
    // does it's job.
    $user = $this->getReference('user:public_meeting_editor');
    assert($user instanceof AccountInterface);
    $this->accountSwitcher->switchTo($user);

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
      'field_media_document' => [[$this->getReference('media_library:Fil:MTM')]],
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

    $node = $node->createDuplicate();
    $node->setTitle('Public meeting with pretix signup and monthly occurrences');
    $node->set('field_pretix_dates', array_map(
      static fn(int $offset) => [
        'location' => sprintf('Location %d', $offset + 1),
        'address' => 'Hack Kampmanns Plads 2, 8000 Aarhus C',
        'registration_deadline_value' => (new \DateTimeImmutable(sprintf('10:00 first day of %d month',
          $offset - 1)))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_from_value' => (new \DateTimeImmutable(sprintf('12:00 first day of %d month',
          $offset - 1)))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_to_value' => (new \DateTimeImmutable(sprintf('13:00 first day of %d month',
          $offset - 1)))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'spots' => 10 * $offset + 7,
      ],
      range(0, 12)
    ));
    $node->save();

    $node = $node->createDuplicate();
    $node->setTitle('Public meeting with pretix signup and daily occurrences');
    $node->set('field_pretix_dates', array_map(
      static fn(int $offset) => [
        'location' => sprintf('Location %d', $offset),
        'address' => 'Hack Kampmanns Plads 2, 8000 Aarhus C',
        'registration_deadline_value' => (new \DateTimeImmutable('10:00 first day of this month'))->modify(sprintf('+%d day',
          $offset - 1))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_from_value' => (new \DateTimeImmutable('12:00 first day of this month'))->modify(sprintf('+%d day',
          $offset - 1))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_to_value' => (new \DateTimeImmutable('13:00 first day of this month'))->modify(sprintf('+%d day',
          $offset - 1))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'spots' => 87,
      ],
      range(1, (int) (new \DateTimeImmutable('this month'))->format('t')),
    ));
    $node->save();

    $node = $node->createDuplicate();
    $node->setTitle('Public meeting with orders');
    $node->set('field_pretix_dates', [
      [
        'location' => 'The location',
        'address' => 'Hack Kampmanns Plads 2, 8000 Aarhus C',
        'registration_deadline_value' => (new \DateTimeImmutable('2024-12-31T00:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_from_value' => (new \DateTimeImmutable('2025-01-01T19:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_to_value' => (new \DateTimeImmutable('2025-01-01T21:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'spots' => 10,
      ],
      [
        'location' => 'Another location',
        'address' => 'Rådhuspladsen 1, 8000 Aarhus C',
        'registration_deadline_value' => (new \DateTimeImmutable('2025-11-30T00:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_from_value' => (new \DateTimeImmutable('2025-12-01T15:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_to_value' => (new \DateTimeImmutable('2025-12-01T16:30:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'spots' => 10,
      ],
      [
        'location' => 'The location',
        'address' => 'Hack Kampmanns Plads 2, 8000 Aarhus C',
        'registration_deadline_value' => (new \DateTimeImmutable('2025-11-30T00:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_from_value' => (new \DateTimeImmutable('2025-12-02T15:00:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'time_to_value' => (new \DateTimeImmutable('2025-12-02T16:30:00+0100'))->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
        'spots' => 10,
      ],
    ]);
    $node->save();

    $this->createOrders($node,
      [
        'lines' => [
          [
            'dates_delta' => 0,
          ],
        ],
      ],
      [
        'email' => 'test-customer@example.com',
        'lines' => [
          [
            'dates_delta' => 1,
            'quantity' => 7,
          ],
        ],
      ],

      // Sell out.
      [
        'lines' => [
          [
            'dates_delta' => 2,
            'quantity' => 10,
          ],
        ],
      ],
    );
  }

  /**
   * Create orders in pretix.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The public meeting node.
   * @param array ...$specs
   *   Order specs, e.g.
   *
   *   <code>
   *   [
   *     [
   *       // Optional email; defaults to 'test@example.com'.
   *       'email' => 'test-customer@example.com',
   *       'lines' => [
   *         [
   *           // The date to create an order line for
   *           'dates_delta' => 0,
   *           // The quantity; defaults to 1.
   *           'quantity' => 9,
   *         ],
   *       ],
   *     ],
   *     [
   *       'lines' => [
   *         [
   *           'dates_delta' => 1,
   *         ],
   *       ],
   *     ],
   *
   *     // Sell out.
   *     [
   *       'lines' => [
   *         [
   *           'dates_delta' => 2,
   *           'quantity' => 10,
   *         ],
   *       ],
   *     ],
   *   ]
   *   </code>.
   */
  public function createOrders(NodeInterface $node, array ...$specs): void {
    $output = 'cli' === PHP_SAPI ? new ConsoleOutput() : new NullOutput();

    if (empty($specs)) {
      throw new \InvalidArgumentException('Missing specs');
    }

    $output->writeln(sprintf('Creating orders for %s (#%d)', $node->label(),
      $node->id()));

    $client = $this->pretixEventHelper->getPretixClient($node);
    $request = new \ReflectionMethod($client, 'request');

    // Add setting to create a proper webhook in pretix
    // (http://pretix.hoeringsportal.local.itkdev.dk/control/organizer/hoeringsportal/webhooks)
    // (see PretixOrderHelper::ensureWebhook() for details).
    $settings = Settings::getAll();
    if (!isset($settings['itk_pretix']['drupal_base_url'])) {
      $settings['itk_pretix']['drupal_base_url'] = 'http://hoeringsportal.local.itkdev.dk:8080';
    }
    new Settings($settings);
    $output->writeln(sprintf('Ensuring that webhook (%s) exists in pretix',
      $settings['itk_pretix']['drupal_base_url']));
    $this->pretixOrderHelper->ensureWebhook($client);

    $eventInfo = $this->pretixEventHelper->loadPretixEventInfo($node);
    /** @var \Drupal\Core\Field\FieldItemListInterface $dates */
    $dates = $node->get('field_pretix_dates');
    foreach ($specs as $spec) {
      $lines = $spec['lines'] ?? NULL;
      if (!is_array($lines)) {
        continue;
      }
      foreach ($spec['lines'] as $line) {
        $date = $dates[$line['dates_delta']] ?? NULL;
        if (!$date) {
          continue;
        }

        $subEventInfo = $this->pretixEventHelper->loadPretixSubEventInfo($date);
        // https://docs.pretix.eu/dev/api/resources/orders.html#creating-orders
        $path = sprintf(
          'organizers/%s/events/%s/orders/',
          $eventInfo['pretix_organizer_slug'],
          $eventInfo['pretix_event_slug'],
        );
        $payload = [
          'json' => [
            'email' => $spec['email'] ?? 'test@example.com',
            'positions' => array_map(
              static fn(int $index) => [
                'subevent' => $subEventInfo['pretix_subevent_id'],
                'attendee_name' => sprintf('Attendee %d', $index),
                'attendee_email' => sprintf('test%03d@example.com', $index),
                'item' => reset($subEventInfo['data']['subevent']['item_price_overrides'])['item'],
              ],
              range(1, $line['quantity'] ?? 1),
            ),
          ],
        ];

        $output->writeln(sprintf('POST\'ing %s to %s', json_encode($payload, JSON_PRETTY_PRINT), $path));
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $request->invoke($client, 'POST', $path, $payload);

        $output->writeln(sprintf('Response: %d', $response->getStatusCode()));
      }
    }
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
      UserFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['nodes'];
  }

}
