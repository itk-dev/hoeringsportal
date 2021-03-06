<?php

namespace Drupal\hoeringsportal_deskpro\Service;

use Drupal\advancedqueue\Entity\Queue;
use Drupal\advancedqueue\Job;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\hoeringsportal_deskpro\Plugin\AdvancedQueue\JobType\SynchronizeHearing;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerInterface;

/**
 * Hearing helper.
 */
class HearingHelper {
  /**
   * Deskpro service.
   *
   * @var array
   */
  private $deskpro;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * Constructs a new DeskproHelper object.
   */
  public function __construct(DeskproService $deskpro, EntityTypeManagerInterface $entityTypeManager, FileSystemInterface $fileSystem, LoggerInterface $logger) {
    $this->deskpro = $deskpro;
    $this->entityTypeManager = $entityTypeManager;
    $this->fileSystem = $fileSystem;
    $this->logger = $logger;
  }

  /**
   * Check if hearing deadline is passed.
   */
  public function isDeadlinePassed(NodeInterface $node) {
    if (!$this->isHearing($node)) {
      return FALSE;
    }

    $deadline = $node->field_reply_deadline->date;

    if (empty($deadline)) {
      return FALSE;
    }

    // Allow users with edit access to do stuff after the deadline.
    if ($node->access('edit', \Drupal::currentUser())) {
      return FALSE;
    }

    return new DrupalDateTime() > new DrupalDateTime($deadline);
  }

  /**
   * Check if node is a hearing.
   */
  public function isHearing($node) {
    return !empty($node) && $node instanceof NodeInterface && 'hearing' === $node->bundle();
  }

  /**
   * Get hearing id.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The hearing node.
   *
   * @return int|null
   *   The hearing id if any.
   */
  public function getHearingId(NodeInterface $node) {
    if (!$this->isHearing($node)) {
      return NULL;
    }

    $prefix = $this->getDeskproConfig()->getHearingIdPrefix();

    return $prefix . $node->id();
  }

  /**
   * Get department id.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The hearing node.
   *
   * @return int|null
   *   The department id if any.
   */
  public function getDepartmentId(NodeInterface $node) {
    return $this->isHearing($node) ? $node->field_deskpro_department_id->value : NULL;
  }

  /**
   * Get tickets from a hearing.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The hearing node.
   *
   * @return array|null
   *   The tickets if any.
   */
  public function getHearingTickets(NodeInterface $node) {
    if (!$this->isHearing($node)) {
      return NULL;
    }

    $data = $node->field_deskpro_data->value;

    return isset($data['tickets']) ? $data['tickets'] : NULL;
  }

  /**
   * Get single ticket from a hearing.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The hearing node.
   * @param int $ticketId
   *   The ticket id.
   *
   * @return array|null
   *   The ticket if is exists.
   */
  public function getHearingTicket(NodeInterface $node, int $ticketId) {
    if (!$this->isHearing($node)) {
      return NULL;
    }

    $tickets = $this->getHearingTickets($node);
    if (is_array($tickets)) {
      foreach ($tickets as $ticket) {
        if ($ticket['id'] === $ticketId) {
          return $ticket;
        }
      }
    }

    return NULL;
  }

  /**
   * Create a hearing ticket.
   */
  public function createHearingTicket(NodeInterface $node, array $data, array $fileIds = []) {
    if (!$this->isHearing($node)) {
      throw new \Exception('Invalid hearing');
    }

    try {
      // Get paths to files.
      $files = array_map(function (File $file) {
        return $this->fileSystem->realpath($file->getFileUri());
      }, File::loadMultiple($fileIds));

      // Map data to custom fields.
      $customFields = $this->deskpro->getTicketCustomFields();
      foreach ($data as $key => $value) {
        if (isset($customFields[$key])) {
          $data['fields'][$customFields[$key]] = $value;
          if (!in_array($key, ['message'])) {
            unset($data[$key]);
          }
        }
      }

      // Add hearing data.
      $data['department'] = (int) $node->field_deskpro_department_id->value;
      $data['fields'][$this->getTicketFieldId('hearing_id')] = $this->getHearingId($node);
      $data['fields'][$this->getTicketFieldId('hearing_name')] = $node->getTitle();
      $data['fields'][$this->getTicketFieldId('edoc_id')] = $node->field_edoc_casefile_id->value;
      $data['fields'][$this->getTicketFieldId('accept_terms')] = TRUE;

      if (isset($node->field_deskpro_agent_email->value)) {
        $data['agent']['email'] = $node->field_deskpro_agent_email->value;
      }

      // Set language.
      $data['language'] = $this->getLanguageId($node);

      // Upload files.
      $blobs = $this->deskpro->uploadFiles($files);
      if ($blobs) {
        $data['fields'][$this->getTicketFieldId('files')] = $this->deskpro->getAttachments($blobs);
      }

      // Create person.
      $response = $this->deskpro->createPerson($data);
      $person = $response->getData();

      $response = $this->deskpro->createTicket($person, $data);
      $ticket = $response->getData();

      $response = $this->deskpro->createMessage($ticket, $data, $blobs);
      $message = $response->getData();

      return [$ticket, $message];
    }
    catch (\Exception $exception) {
      $httpRequestException = $this->deskpro->getLastHttpRequestException();
      $this->logger->critical('@message: @body', [
        '@message' => $exception->getMessage(),
        'data' => $data,
        '@body' => (string) $httpRequestException->getResponse()->getBody(),
        '@request_message' => $httpRequestException->getMessage(),
      ]);
      throw $exception;
    }
  }

  /**
   * Get Deskpro language id for a hearing.
   */
  private function getLanguageId(NodeInterface $node) {
    try {
      $response = $this->deskpro->getLanguages();

      $locale = $node->language()->getId();
      foreach ($response->getData() as $language) {
        if ($language['locale'] === $locale) {
          return $language['id'];
        }
      }
    }
    catch (\Exception $exception) {
    }

    return 1;
  }

  /**
   * Get a Deskpro custom field id.
   *
   * @param string $field
   *   The field name.
   *
   * @return int
   *   The field id.
   */
  public function getTicketFieldId(string $field) {
    return $this->deskpro->getTicketFieldId($field);
  }

  /**
   * Get a Deskpro custom field name.
   *
   * @param string $field
   *   The field name.
   * @param string $prefix
   *   The field prefix.
   *
   * @return string
   *   The field name.
   */
  public function getTicketFieldName($field, string $prefix = 'ticket_field_') {
    return $prefix . $this->getTicketFieldId($field);
  }

  /**
   * Get ticket url.
   *
   * @param array $ticket
   *   The ticket.
   *
   * @return string
   *   The url of the ticket.
   */
  public function getTicketUrl(array $ticket) {
    return $this->deskpro->getDeskproUrl('/tickets/{ref}', ['ref' => $ticket['ref']]);
  }

  /**
   * Replace tokens.
   */
  public function replaceTokens($text, $data) {
    $pattern = '/\[(?P<type>[^:]+):(?P<key>[^\]]+)\]/';

    return preg_replace_callback($pattern, function ($matches) use ($data) {
        $type = $matches['type'];
        $key = $matches['key'];
        return isset($data[$type][$key]) ? $data[$type][$key] : $matches[0];
    }, $text);
  }

  /**
   * Get data synchronization url.
   */
  public function getDataSynchronizationUrl($delayed = FALSE) {
    $params = $delayed ? ['delayed' => TRUE] : [];
    return Url::fromRoute('hoeringsportal_deskpro.data.synchronize.hearing', $params, ['absolute' => TRUE])->toString();
  }

  /**
   * Get data synchronization headers.
   */
  public function getDataSynchronizationHeaders() {
    return [
      'x-deskpro-token' => $this->deskpro->getToken(),
    ];
  }

  /**
   * Get data synchronization payload.
   */
  public function getDataSynchronizationPayload($ticketId = -87) {
    return [
      'ticket' => [
        $this->getTicketFieldName('hearing_id', 'field') => $ticketId,
      ],
    ];
  }

  /**
   * Get data from Deskpro and store in hearing node.
   */
  public function synchronizeHearing(array $payload = NULL, $delayed = FALSE) {
    $hearingIdfieldName = 'field' . $this->deskpro->getTicketHearingIdFieldId();
    if (!isset($payload['ticket'][$hearingIdfieldName])) {
      throw new \Exception('Invalid data');
    }

    $hearingId = $payload['ticket'][$hearingIdfieldName];

    $prefix = $this->getDeskproConfig()->getHearingIdPrefix();
    if ($prefix && 0 === strpos($hearingId, $prefix)) {
      $hearingId = substr($hearingId, strlen($prefix));
    }

    $hearing = Node::load($hearingId);
    if (!$this->isHearing($hearing)) {
      throw new \Exception('Invalid hearing: ' . $hearingId);
    }

    if ($delayed) {
      $job = Job::create(SynchronizeHearing::class, $payload);
      $queue = Queue::load('hoeringsportal_deskpro');
      $delay = max($this->deskpro->getConfig()->getSynchronizationDelay(), 60);
      $queue->enqueueJob($job, $delay);

      return $job->toArray();
    }

    return $this->synchronizeHearingTickets($hearing);
  }

  /**
   * Synchronize hearing tickets.
   */
  public function synchronizeHearingTickets(Node $hearing) {
    if (!$this->isHearing($hearing)) {
      throw new \Exception('Invalid hearing: ' . $hearing->id());
    }

    $deskproHearingId = $this->getHearingId($hearing);

    // The Deskpro API supports returning maximum 200 items per request.
    $itemsPerPage = 100;
    $currentPage = 1;
    $totalPages = NULL;

    // Get all tickets.
    $tickets = [];
    do {
      $result = $this->deskpro->getHearingTickets($deskproHearingId, [
        'expand' => ['fields', 'person', 'messages', 'attachments'],
        'no_cache' => 1,
        'count' => $itemsPerPage,
        'page' => $currentPage,
      ]);
      $tickets[] = $result->getData();

      $meta = $result->getMeta();

      if (!isset($meta['pagination']['current_page'], $meta['pagination']['total_pages'])) {
        break;
      }

      $currentPage = $meta['pagination']['current_page'] + 1;
      $totalPages = $meta['pagination']['total_pages'];
    } while ($currentPage <= $totalPages);

    $data = [
      'tickets' => array_merge(...$tickets),
    ];

    $hearing->field_deskpro_data->value = json_encode($data);
    $hearing->save();

    return ['hearing' => $hearing->id(), 'data' => $data];
  }

  /**
   * The Deskpro config.
   *
   * @return \Drupal\hoeringsportal_deskpro\State\DeskproConfig
   *   The Deskpro config.
   */
  public function getDeskproConfig() {
    return $this->deskpro->getConfig();
  }

}
