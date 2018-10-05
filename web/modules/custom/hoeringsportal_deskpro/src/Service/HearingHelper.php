<?php

namespace Drupal\hoeringsportal_deskpro\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
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

    $deadline = $node->field_reply_deadline->value;

    if (empty($deadline)) {
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
    return $this->isHearing($node) ? $node->id() : NULL;
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
          unset($data[$key]);
        }
      }

      // Add hearing data.
      $data['fields'][$this->getTicketFieldId('hearing_id')] = $node->id();
      $data['fields'][$this->getTicketFieldId('hearing_name')] = $node->getTitle();
      if (isset($node->field_deskpro_agent_email->value)) {
        $data['agent']['email'] = $node->field_deskpro_agent_email->value;
      }

      // Create person.
      $response = $this->deskpro->createPerson($data);
      $person = $response->getData();

      $response = $this->deskpro->createTicket($person, $data);
      $ticket = $response->getData();
      $response = $this->deskpro->createMessage($ticket, $data, $files);
      $message = $response->getData();

      return [$ticket, $message];
    }
    catch (\Exception $exception) {
      $this->logger->critical('', [
        'data' => $data,
        'body' => (string) $this->deskpro->getLastHttpRequestException()->getResponse()->getBody(),
        'message' => $this->deskpro->getLastHttpRequestException()->getMessage(),
      ]);
      throw $exception;
    }
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
  public function getDataSynchronizationUrl() {
    return Url::fromRoute('hoeringsportal_deskpro.data.synchronize.hearing', [], ['absolute' => TRUE])->toString();
  }

  /**
   * Get data synchronization headers.
   */
  public function getDataSynchronizationHeaders() {
    return [
      'x-deskpro-token:' => $this->deskpro->getToken(),
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
  public function synchronizeHearing(array $payload = NULL) {
    $hearingIdfieldName = 'field' . $this->deskpro->getTicketHearingIdFieldId();
    if (!isset($payload['ticket'][$hearingIdfieldName])) {
      throw new \Exception('Invalid data');
    }
    $hearingId = $payload['ticket'][$hearingIdfieldName];

    $hearing = Node::load($hearingId);
    if (NULL === $hearing) {
      throw new \Exception('Invalid hearing: ' . $hearingId);
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

    $result = $this->deskpro->getHearingTickets($hearing->id(), [
      'expand' => ['fields', 'person', 'messages', 'attachments'],
      'no_cache' => 1,
      'count' => 100,
    ]);
    $data = [
      'tickets' => $result->getData(),
    ];

    $hearing->field_deskpro_data->value = json_encode($data);
    $hearing->save();

    return ['hearing' => $hearing->id(), 'data' => $data];
  }

}
