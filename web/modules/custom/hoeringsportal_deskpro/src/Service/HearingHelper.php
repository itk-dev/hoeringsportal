<?php

namespace Drupal\hoeringsportal_deskpro\Service;

use Drupal\advancedqueue\Entity\Queue;
use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\Plugin\AdvancedQueue\Backend\SupportsDeletingJobsInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Merge;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\hoeringsportal_deskpro\Plugin\AdvancedQueue\JobType\SynchronizeTicket;
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
   * The database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * Constructs a new DeskproHelper object.
   */
  public function __construct(DeskproService $deskpro, EntityTypeManagerInterface $entityTypeManager, FileSystemInterface $fileSystem, Connection $database, LoggerInterface $logger) {
    $this->deskpro = $deskpro;
    $this->entityTypeManager = $entityTypeManager;
    $this->fileSystem = $fileSystem;
    $this->database = $database;
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
  private function getHearingId(NodeInterface $node) {
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
        return $data[$type][$key] ?? $matches[0];
    }, $text);
  }

  /**
   * Get ticket synchronization url.
   */
  public function getTicketSynchronizationUrl() {
    return Url::fromRoute('hoeringsportal_deskpro.data.synchronize.ticket', [], ['absolute' => TRUE])->toString();
  }

  /**
   * Get ticket synchronization headers.
   */
  public function getDataSynchronizationHeaders() {
    return [
      'x-deskpro-token' => $this->deskpro->getToken(),
    ];
  }

  /**
   * Get ticket synchronization payload.
   */
  public function getTicketSynchronizationPayload(int $hearingId, int $ticketId) {
    return [
      'ticket' => [
        'id' => $ticketId,
        $this->getTicketFieldName('hearing_id', 'field') => $hearingId,
      ],
    ];
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

    $this->setDeskproData($hearing, $data);
    $hearing->save();

    return ['hearing' => $hearing->id(), 'data' => $data];
  }

  /**
   * Get data from Deskpro and store in hearing node.
   */
  public function synchronizeTicket(array $payload, $delayed = TRUE) {
    [$hearing, $ticketId] = $this->validateTicketPayload($payload);
    if ($delayed) {
      $queue = Queue::load('hoeringsportal_deskpro');
      $delay = max($this->deskpro->getConfig()->getSynchronizationDelay(), 60);

      // Remove any already queued jobs for same hearing and ticket from the
      // queue.
      $backend = $queue->getBackend();
      $hearingIdFieldName = $this->getHearingIdFieldName();
      $matchesCurrentPayload = function (array $otherPayload) use ($payload, $hearingIdFieldName) {
        // Payloads must match on both ticket.id and ticket.$hearingIdfieldName.
        return isset($otherPayload['ticket']['id'], $otherPayload['ticket'][$hearingIdFieldName])
          && $payload['ticket']['id'] === $otherPayload['ticket']['id']
          && $payload['ticket'][$hearingIdFieldName] === $otherPayload['ticket'][$hearingIdFieldName];
      };

      if ($backend instanceof SupportsDeletingJobsInterface) {
        /** @var \Drupal\advancedqueue\Job $job */
        foreach ($backend->getJobs(Job::STATE_QUEUED) as $job) {
          if ($matchesCurrentPayload($job->getPayload())) {
            $backend->deleteJob($job->getId());
          }
        }
      }
      $job = Job::create(SynchronizeTicket::class, $payload);
      $queue->enqueueJob($job, $delay);

      return $job->toArray();
    }

    return $this->runSynchronizeTicket($payload);
  }

  /**
   * Synchronize hearing tickets.
   */
  public function runSynchronizeTicket(array $payload) {
    /** @var \Drupal\node\Entity\NodeInterface $hearing */
    [$hearing, $ticketId] = $this->validateTicketPayload($payload);

    $ticket = $this->deskpro->getTicket($ticketId, [
      'expand' => ['fields', 'person', 'messages', 'attachments'],
      'no_cache' => 1,
    ])->getData();

    // Check that ticket belongs to hearing.
    $ticketHearingId = $ticket['fields']['hearing_id'] ?? NULL;
    if (NULL === $ticketHearingId || (int) $ticketHearingId !== (int) $hearing->id()) {
      $message = sprintf('Ticket %s does not belong to hearing %s (belongs to hearing %s)', $ticketId, $hearing->id(), $ticketHearingId);
      $this->logger->error($message);
      throw new \RuntimeException($message);
    }

    $keys = [
      'entity_type' => 'node',
      'bundle' => $hearing->bundle(),
      'entity_id' => $hearing->id(),
      'ticket_id' => $ticketId,
    ];

    // Remove unpublished or deleted tickets.
    $remove =
      // Ticket is unpublished if and only if the field "unpublish_reply" === 1.
      1 === (int) ($ticket['fields']['unpublish_reply'] ?? NULL)
      // A deleted ticket has a status starting with "hidden" (and is not really
      // deleted so it makes sense to get it from the api).
      || 0 === strpos($ticket['status'], 'hidden');
    if ($remove) {
      // Remove ticket (if it exists).
      $query = $this->database->delete('hoeringsportal_deskpro_deskpro_tickets');
      foreach ($keys as $field => $value) {
        $query->condition($field, $value);
      }
      $result = $query->execute();

      if (1 === $result) {
        $this->logger->debug(
          sprintf('Ticket %d removed from hearing %s', $ticketId, $hearing->id()),
          [
            '@payload' => json_encode($payload),
          ]
        );
      }
    }
    else {
      $fields = [
        'updated_at' => (new DrupalDateTime())->format(DrupalDateTime::FORMAT),
        'data' => json_encode($ticket),
      ];

      $result = $this->database->merge('hoeringsportal_deskpro_deskpro_tickets')
        ->keys($keys)
        ->updateFields($fields)
        ->insertFields(
          $keys + $fields + [
            'created_at' => $fields['updated_at'],
          ])
        ->execute();

      $this->logger->debug(
        $result === Merge::STATUS_INSERT
        ? sprintf('Ticket %s added on hearing %s', $ticketId, $hearing->id())
        : sprintf('Ticket %s updated on hearing %s', $ticketId, $hearing->id()),
        [
          '@ticket' => json_encode($ticket),
        ]
      );

    }

    Cache::invalidateTags($hearing->getCacheTags());

    return ['hearing' => $hearing->id(), 'ticket' => $ticket];
  }

  /**
   * Validate ticket payload.
   *
   * @param mixed $payload
   *   The payload.
   *
   * @return array
   *   The hearing node and ticket id ([NodeInterface, string]).
   *
   * @throws \RuntimeException
   */
  private function validateTicketPayload($payload): array {
    if (!is_array($payload)) {
      throw new \RuntimeException(sprintf('Payload must be an array; got %s', gettype($payload)));
    }

    $ticketId = $payload['ticket']['id'] ?? NULL;
    if (NULL === $ticketId) {
      throw new \RuntimeException('Missing ticket id');
    }

    $hearingIdFieldName = $this->getHearingIdFieldName();
    if (!isset($payload['ticket'][$hearingIdFieldName])) {
      throw new \RuntimeException(sprintf('Missing hearing id (%s)', $hearingIdFieldName));
    }

    $hearingId = $payload['ticket'][$hearingIdFieldName];

    $prefix = $this->getDeskproConfig()->getHearingIdPrefix();
    if ($prefix && 0 === strpos($hearingId, $prefix)) {
      $hearingId = substr($hearingId, strlen($prefix));
    }

    $hearing = Node::load($hearingId);
    if (!$this->isHearing($hearing)) {
      throw new \RuntimeException('Invalid hearing: ' . $hearingId);
    }

    return [$hearing, $ticketId];
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

  /**
   * Get hearing tickets count.
   */
  public function getHearingTicketsCount(NodeInterface $node, bool $reset = FALSE): int {
    if (!$this->isHearing($node)) {
      throw new \RuntimeException('Invalid hearing: ' . $node->id());
    }

    $info = &drupal_static(__METHOD__, []);

    $bundle = $node->bundle();
    $entity_id = $node->id();
    if ($reset || !isset($info[$bundle][$entity_id])) {
      $query = $this->database
        ->select('hoeringsportal_deskpro_deskpro_tickets', 't')
        ->condition('entity_type', 'node', '=')
        ->condition('entity_id', $node->id(), '=')
        ->condition('bundle', $node->bundle(), '=');
      $query->addExpression('COUNT(*)');

      $info[$bundle][$entity_id] = $query->execute()->fetchField();
    }

    return $info[$bundle][$entity_id];
  }

  /**
   * Get hearing tickets.
   */
  public function getHearingTickets(NodeInterface $node, bool $reset = FALSE): ?array {
    if (!$this->isHearing($node)) {
      throw new \RuntimeException('Invalid hearing: ' . $node->id());
    }

    $info = &drupal_static(__METHOD__, []);

    $bundle = $node->bundle();
    $entity_id = $node->id();
    if ($reset || !isset($info[$bundle][$entity_id])) {
      $records = $this->database
        ->select('hoeringsportal_deskpro_deskpro_tickets', 't')
        ->fields('t')
        ->condition('entity_type', 'node', '=')
        ->condition('entity_id', $node->id(), '=')
        ->condition('bundle', $node->bundle(), '=')
        ->orderBy('created_at', 'DESC')
        ->execute()
        ->fetchAll();

      $info[$bundle][$entity_id] = array_map(static function ($record) {
        return json_decode($record->data, TRUE);
      }, $records);
    }

    return $info[$bundle][$entity_id] ?? NULL;
  }

  /**
   * Get all tickets.
   */
  public function getTickets(): ?array {
    $records = $this->database
      ->select('hoeringsportal_deskpro_deskpro_tickets', 't')
      ->fields('t')
      ->orderBy('created_at', 'DESC')
      ->execute()
      ->fetchAll();

    return [];
  }

  /**
   * Get Deskpro ticket.
   */
  public function getDeskproTicket(NodeInterface $node, int $ticketId, bool $reset = FALSE): ?array {
    if (!$this->isHearing($node)) {
      throw new \RuntimeException('Invalid hearing: ' . $node->id());
    }

    $info = &drupal_static(__METHOD__, []);

    $bundle = $node->bundle();
    $entity_id = $node->id();
    if ($reset || !isset($info[$bundle][$entity_id][$ticketId])) {
      $record = $this->database
        ->select('hoeringsportal_deskpro_deskpro_tickets', 't')
        ->fields('t')
        ->condition('entity_type', 'node', '=')
        ->condition('entity_id', $node->id(), '=')
        ->condition('bundle', $node->bundle(), '=')
        ->condition('ticket_id', $ticketId, '=')
        ->execute()
        ->fetch();

      $info[$bundle][$entity_id][$ticketId] = empty($record) ? NULL : json_decode($record->data, TRUE);
    }

    return $info[$bundle][$entity_id][$ticketId] ?? NULL;
  }

  /**
   * Set Deskpro data for a node.
   *
   * @param \Drupal\node\Entity\NodeInterface $node
   *   The node.
   * @param array $data
   *   The data.
   */
  private function setDeskproData(NodeInterface $node, array $data) {
    $this->database->merge('hoeringsportal_deskpro_deskpro_data')
      ->keys([
        'entity_type' => 'node',
        'entity_id' => $node->id(),
        'bundle' => $node->bundle(),
      ])
      ->fields([
        'data' => json_encode($data),
      ])
      ->execute();
  }

  /**
   * Get hearing id field name.
   */
  private function getHearingIdFieldName(): string {
    return 'field' . $this->deskpro->getTicketHearingIdFieldId();
  }

}
