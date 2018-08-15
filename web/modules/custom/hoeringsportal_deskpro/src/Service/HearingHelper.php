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
   * Get data from Deskpro and store in hearing node.
   */
  public function syncronizeHearing(array $payload = NULL) {
    $hearingIdfieldName = 'field' . $this->deskpro->getTicketHearingIdFieldId();
    if (!isset($payload['ticket'][$hearingIdfieldName])) {
      throw new \Exception('Invalid data');
    }
    $hearingId = $payload['ticket'][$hearingIdfieldName];

    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage
      ->getQuery()
      ->condition('type', 'hearing')
      ->condition('field_deskpro_hearing_id', $hearingId)
      ->execute();
    $hearings = $storage->loadMultiple($ids);

    $result = $this->deskpro->getHearingTickets($hearingId, [
      'expand' => ['person', 'messages'],
      'no_cache' => 1,
    ]);
    $data = json_encode(['tickets' => $result->getData()]);

    foreach ($hearings as $hearing) {
      $hearing->get('field_deskpro_data')->set(0, $data);
      $hearing->save();
    }

    return ['hearings' => array_keys($hearings), 'data' => $data];
  }

}
