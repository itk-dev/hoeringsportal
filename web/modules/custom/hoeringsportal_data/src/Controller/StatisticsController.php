<?php

namespace Drupal\hoeringsportal_data\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\hoeringsportal_data\Form\StatisticsForm;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper;
use Drupal\itk_pretix\Pretix\EventHelper;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Controller for statistics.
 */
final class StatisticsController extends ControllerBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    #[Autowire(service: 'itk_pretix.event_helper')]
    private readonly EventHelper $pretixEventHelper,
    #[Autowire(service: 'hoeringsportal_deskpro.helper')]
    private readonly HearingHelper $hearingHelper,
    private readonly UrlGeneratorInterface $urlGenerator,
    #[Autowire(service: 'serializer')]
    private readonly SerializerInterface $serializer,
    #[Autowire(service: 'file.mime_type.guesser')]
    private readonly MimeTypeGuesserInterface $mimeTypeGuesser,
  ) {
  }

  /**
   * Index.
   */
  public function index(Request $request) {
    $data = $this->getData($request);

    if (NULL !== $data) {
      [$report, $data] = $data;
      $format = $request->query->get('export');
      if (NULL !== $format) {
        return $this->export($report, $data, $format);
      }
    }

    $form = $this->formBuilder()->getForm(StatisticsForm::class, ['reports' => $this->getReports()]);
    unset($form['build_id']);

    return [
      'form' => $form,
      'result' => [
        '#theme' => 'hoeringsportal_data_statistics_export',
        '#data' => $data,
        '#options' => [
          'url_field' => (string) $this->t('Url'),
          'title_field' => (string) $this->t('Title'),
        ],
      ],
    ];
  }

  /**
   * Export data.
   */
  private function export(array $report, array $data, string $format = 'csv'): Response {
    $filename = 'export.' . $format;
    if (isset($report['export_filename_template'])) {
      $tokens = [
        'date' => (new \DateTimeImmutable())->format('Y-m-d\TH-i-s'),
        'title' => $report['title'],
        'format' => $format,
      ];
      // Replace placeholder on the form `{key}` in file name template.
      $filename = preg_replace_callback(
        '/\{([^}]+)\}/',
        static fn (array $matches): string => $tokens[$matches[1]] ?? $matches[0],
        $report['export_filename_template'],
      );
    }
    $response = new Response($this->serializer->serialize($data, $format));

    $contentType = $this->mimeTypeGuesser->guessMimeType($filename);
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
  private function getData(Request $request): ?array {
    $values = $request->query->all();
    $requestedReport = $request->query->get(StatisticsForm::REPORT);
    $reports = $this->getReports();
    $report = $reports[$requestedReport] ?? NULL;

    if ($requestedReport && !isset($report['callback'])) {
      throw new NotFoundHttpException();
    }
    $run = isset($values['show']) || isset($values['export']);
    if ($run) {
      $startDate = new \DateTimeImmutable('first day of january');
      try {
        $startDate = new \DateTimeImmutable($values['start_date']);
      }
      catch (\Exception $exception) {
      }
      $endDate = $startDate->modify('midnight last day of december');
      try {
        $endDate = new \DateTimeImmutable($values['end_date']);
      }
      catch (\Exception $exception) {
      }

      $parameters = [
        'start_date' => $startDate,
        'end_date' => $endDate,
      ];

      return [$report, $report['callback']($parameters)];
    }

    return NULL;
  }

  /**
   * Get hearing data.
   */
  private function getHearings(array $parameters) {
    $startDate = $parameters['start_date'];
    $endDate = $parameters['end_date'];
    // Make end date inclusive.
    if ($endDate instanceof \DateTimeImmutable) {
      $endDate = $endDate->modify('+1 day');
    }
    elseif ($endDate instanceof \DateTime) {
      $endDate->modify('+1 day');
    }

    $nids = $this->entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'hearing')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('field_start_date', [
        $startDate->format(DateTimeItemInterface::DATE_STORAGE_FORMAT),
        $endDate->format(DateTimeItemInterface::DATE_STORAGE_FORMAT),
      ], 'BETWEEN')
      ->sort('field_start_date', 'ASC')
      ->sort('nid', 'ASC')
      ->execute();

    return $this->buildData(
      $nids,
      function (NodeInterface $node) {
        try {
          $numberOfReplies = $this->hearingHelper->getHearingTicketsCount($node);
        }
        catch (\Exception $exception) {
          $numberOfReplies = NULL;
        }

        return [
          'Id' => $node->id(),
          'Title' => $node->getTitle(),
          'Start time' => $node->field_start_date->value,
          'Reply deadline' => $node->field_reply_deadline->value,
          'Number of replies' => $numberOfReplies,
          'Url' => $this->getNodeUrl($node),
        ];
      });
  }

  /**
   * Get public meeting data.
   */
  private function getPublicMeetings(array $parameters) {
    $startDate = $parameters['start_date'];
    $endDate = $parameters['end_date'];
    // Make end date inclusive.
    if ($endDate instanceof \DateTimeImmutable) {
      $endDate = $endDate->modify('+1 day');
    }
    elseif ($endDate instanceof \DateTime) {
      $endDate->modify('+1 day');
    }

    $nids = $this->entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'public_meeting')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('field_first_meeting_time', [
        $startDate->format(DateTimeItemInterface::DATE_STORAGE_FORMAT),
        $endDate->format(DateTimeItemInterface::DATE_STORAGE_FORMAT),
      ], 'BETWEEN')
      ->sort('field_first_meeting_time', 'ASC')
      ->sort('nid', 'ASC')
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
          'Url' => $this->getNodeUrl($node),
        ];
      });
  }

  /**
   * Get node URL.
   */
  private function getNodeUrl(NodeInterface $node): string {
    return Url::fromRoute(
      'entity.node.canonical',
      ['node' => $node->id()]
    )
      ->setAbsolute()
      ->setOptions([
        // We want a real URL with a node ID in it.
        'alias' => TRUE,
      ])
      ->toString(TRUE)
      ->getGeneratedUrl();
  }

  /**
   * Build data from node ids.
   */
  private function buildData(array $nids, callable $callback) {
    $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($nids);
    $data = array_map($callback, array_values($nodes));

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
   * Get all reports.
   *
   * @return array[]
   *   The list of reports.
   */
  private function getReports(): array {
    return [
      'hearing' => [
        'title' => $this->t('Hearing'),
        'callback' => $this->getHearings(...),
        'export_filename_template' => 'hearing-{date}.{format}',
      ],
      'public_meeting' => [
        'title' => $this->t('Public meeting'),
        'callback' => $this->getPublicMeetings(...),
        'export_filename_template' => 'public_meeting-{date}.{format}',
      ],
    ];
  }

}
