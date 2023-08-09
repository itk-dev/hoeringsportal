<?php

namespace Drupal\hoeringsportal_data\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\hoeringsportal_data\Form\StatisticsForm;
use Drupal\itk_pretix\Pretix\EventHelper;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Controller for statistics.
 */
class StatisticsController extends ControllerBase {
  use StringTranslationTrait;

  private const DATE_FORMAT = 'Y-m-d';

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  private $serializer;

  /**
   * The mime type guesser.
   *
   * @var \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface
   */
  private $mimeTypeGuesser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EventHelper $pretixEventHelper,
    UrlGeneratorInterface $urlGenerator,
    SerializerInterface $serializer,
    MimeTypeGuesserInterface $mimeTypeGuesser
  ) {
    $this->pretixEventHelper = $pretixEventHelper;
    $this->urlGenerator = $urlGenerator;
    $this->serializer = $serializer;
    $this->mimeTypeGuesser = $mimeTypeGuesser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('itk_pretix.event_helper'),
      $container->get('url_generator'),
      $container->get('serializer'),
      $container->get('file.mime_type.guesser')
    );
  }

  /**
   * Index.
   */
  public function index(Request $request) {
    $data = $this->getData($request);

    if (NULL !== $data) {
      $format = $request->query->get('export');
      if (NULL !== $format) {
        return $this->export($data);
      }
    }

    return [
      'header' => [
        '#markup' => $this->t('Statistics'),
        '#prefix' => '<h1>',
        '#suffix' => '</h1>',
      ],
      'form' => \Drupal::formBuilder()->getForm(StatisticsForm::class),
      'result' => [
        '#theme' => 'hoeringsportal_data_statistics_export',
        '#data' => $data,
        '#options' => [
          'url_field' => (string) $this->t('Url'),
          'title_field' => (string) $this->t('Title'),
        ],
      ],

      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  /**
   * Export data.
   */
  private function export(array $data, string $format = 'csv'): Response {
    $filename = 'export.' . $format;
    $response = new Response($this->serializer->serialize($data, $format));

    $contentType = $this->mimeTypeGuesser->guess($filename);
    if (NULL !== $contentType) {
      $response->headers->set(
        'content-type',
        'text/csv'
      );
    }

    $response->headers->set(
      'content-disposition',
      $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        $filename
      )
    );

    return $response;
  }

  /**
   * Get data.
   */
  private function getData(Request $request) {
    $values = $request->query->all();
    $contentType = $values['content_type'] ?? NULL;
    $run = isset($values['show']) || isset($values['export']);
    if ($run && $contentType) {
      $startDate = new \DateTimeImmutable('first day of january');
      try {
        $startDate = new \DateTimeImmutable($values['start_date']);
      }
      catch (\Exception $exception) {
      }
      $endDate = $startDate->add(new \DateInterval('P1Y'));
      try {
        $endDate = new \DateTimeImmutable($values['end_date']);
      }
      catch (\Exception $exception) {
      }

      $parameters = [
        'start_date' => $startDate,
        'end_date' => $endDate,
      ];

      switch ($contentType) {
        case 'hearing':
          return $this->getHearings($parameters);

        case 'public_meeting':
          return $this->getPublicMeetings($parameters);
      }
    }

    return NULL;
  }

  /**
   * Get hearing data.
   */
  private function getHearings(array $parameters) {
    $startDate = $parameters['start_date'];
    $endDate = $parameters['end_date'];
    $nids = $this->entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'hearing')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('field_start_date', [
        $startDate->format(static::DATE_FORMAT),
        $endDate->format(static::DATE_FORMAT),
      ], 'BETWEEN')
      ->sort('field_start_date', 'ASC')
      ->execute();

    return $this->buildData(
      $nids,
      function (NodeInterface $node) {
        try {
          $tickets = json_decode($node->field_deskpro_data->value, TRUE)['tickets'];
        }
        catch (\Exception $exception) {
          $tickets = [];
        }

        return [
          'Id' => $node->id(),
          'Title' => $node->getTitle(),
          'Start time' => $node->field_start_date->value,
          'Reply deadline' => $node->field_reply_deadline->value,
          'Number of replies' => count($tickets),
          'Url' => $this->urlGenerator->generateFromRoute(
            'entity.node.canonical',
            ['node' => $node->id()],
            [
              'absolute' => TRUE,
              'alias' => TRUE,
            ]
          ),
        ];
      });
  }

  /**
   * Get public meeting data.
   */
  private function getPublicMeetings(array $parameters) {
    $startDate = $parameters['start_date'];
    $endDate = $parameters['end_date'];
    $nids = $this->entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'public_meeting')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('field_first_meeting_time', [
        $startDate->format(static::DATE_FORMAT),
        $endDate->format(static::DATE_FORMAT),
      ], 'BETWEEN')
      ->sort('field_start_date', 'ASC')
      ->execute();

    return $this->buildData(
      $nids,
      function (NodeInterface $node) {
        $numberOfAttendees = NULL;
        $maxNumberOfAttendees = NULL;
        $data = $this->pretixEventHelper->loadPretixEventInfo($node);
        $availability = $data['data']['quotas'][0]['availability'] ?? NULL;
        if (NULL !== $availability) {
          $maxNumberOfAttendees = $availability['total_size'];
          $numberOfAttendees = $availability['total_size'] - $availability['available_number'];
        }

        return [
          'Id' => $node->id(),
          'Title' => $node->getTitle(),
          'Start time' => $node->field_first_meeting_time->value,
          'End time' => $node->field_last_meeting_time->value,
          'Number of attendees' => $numberOfAttendees,
          'Max number of attendees' => $maxNumberOfAttendees,
          'Url' => $this->urlGenerator->generateFromRoute(
            'entity.node.canonical',
            ['node' => $node->id()],
            [
              'absolute' => TRUE,
              'alias' => TRUE,
            ]
          ),
        ];
      });
  }

  /**
   * Build data from node ids.
   */
  private function buildData(array $nids, callable $callback) {
    $data = array_map($callback, array_values($this->loadNodes($nids)));

    // Translate keys.
    foreach ($data as &$item) {
      foreach ($item as $key => $value) {
        // We must unset before settings the translated key to properly handle
        // missing translations.
        unset($item[$key]);
        $item[(string) $this->t($key)] = $value; // phpcs:ignore
      }
    }

    return $data;
  }

  /**
   * Load nodes from sorted nodes ids.
   *
   * @param array $nids
   *   The node ids.
   *
   * @return array|NodeInterface[]
   *   The nodes.
   */
  private function loadNodes(array $nids) {
    return array_map(static function ($nid) {
      return Node::load($nid);
    }, $nids);
  }

}
