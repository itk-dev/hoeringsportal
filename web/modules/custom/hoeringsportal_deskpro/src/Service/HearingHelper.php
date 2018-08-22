<?php

namespace Drupal\hoeringsportal_deskpro\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

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
   * Constructs a new DeskproHelper object.
   */
  public function __construct(DeskproService $deskpro, EntityTypeManagerInterface $entityTypeManager) {
    $this->deskpro = $deskpro;
    $this->entityTypeManager = $entityTypeManager;
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
   * Get data from Deskpro and store in hearing node.
   */
  public function syncronizeHearing(array $payload = NULL) {
    $hearingIdfieldName = 'field' . $this->deskpro->getTicketHearingIdFieldId();
    if (!isset($payload['ticket'][$hearingIdfieldName])) {
      throw new \Exception('Invalid data');
    }
    $hearingId = $payload['ticket'][$hearingIdfieldName];

    $hearing = $this->entityTypeManager->getStorage('node')->load($hearingId);
    if (!$this->isHearing($hearing)) {
      throw new \Exception('Invalid hearing: ' . $hearingId);
    }

    $result = $this->deskpro->getHearingTickets($hearing->id(), [
      'expand' => ['fields', 'person', 'messages', 'attachments'],
      'no_cache' => 1,
    ]);
    $data = [
      'tickets' => $result->getData(),
    ];

    $hearing->field_deskpro_data->value = json_encode($data);
    $hearing->save();

    return ['hearing' => $hearing->id(), 'data' => $data];
  }

}
