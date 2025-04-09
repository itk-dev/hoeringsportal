<?php

namespace Drupal\hoeringsportal_data\Helper;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper as DeskproHearingHelper;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Hearing helper.
 */
class HearingHelper implements LoggerAwareInterface {
  use LoggerAwareTrait;

  const NODE_TYPE_HEARING = 'hearing';
  const STATE_UPCOMING = 'upcoming';
  const STATE_ACTIVE = 'active';
  const STATE_FINISHED = 'finished';

  /**
   * Constructor.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly DeskproHearingHelper $deskproHearingHelper,
    LoggerChannelInterface $logger,
  ) {
    $this->setLogger($logger);
  }

  /**
   * Get current hearing state.
   */
  public function getState(NodeInterface $hearing) {
    if (!$this->isHearing($hearing)) {
      return NULL;
    }

    return $hearing->field_content_state->value;
  }

  /**
   * Set hearing state.
   */
  public function setState(NodeInterface $hearing, $state) {
    $hearing->field_content_state->value = $state;

    return $hearing;
  }

  /**
   * Compute hearing state.
   */
  public function computeState(NodeInterface $hearing) {
    if (!$this->isHearing($hearing)) {
      return NULL;
    }

    $now = $this->getDateTime();
    $startTime = $hearing->field_start_date->date;
    $endTime = $hearing->field_reply_deadline->date;

    if (empty($startTime) || $startTime > $now) {
      return self::STATE_UPCOMING;
    }

    if (!empty($endTime) && $endTime < $now) {
      return self::STATE_FINISHED;
    }

    return self::STATE_ACTIVE;
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

    return $this->getDateTime() > new DrupalDateTime($deadline);
  }

  /**
   * Find hearings whose replies must be deleted.
   *
   * @return array
   *   A list of hearing ids.
   */
  public function findHearingWhoseRepliesMustBeDeleted(DrupalDateTime $from, DrupalDateTime $to): array {
    try {
      return $this->entityTypeManager
        ->getStorage('node')
        ->getQuery()
        ->condition('type', 'hearing')
        ->condition('field_delete_date', [
          $from->format(DateTimeItemInterface::DATE_STORAGE_FORMAT),
          $to->format(DateTimeItemInterface::DATE_STORAGE_FORMAT),
        ], 'BETWEEN')
        ->accessCheck(FALSE)
        ->execute();
    }
    catch (\Exception $exception) {
      $this->logger->error('Error finding hearing whose replies must be deleted: @message', [
        '@message' => $exception->getMessage(),
      ]);

      return [];
    }
  }

  /**
   * A list of conditions.
   *
   * @var array
   */
  private $defaultConditions = [
    ['status', NodeInterface::PUBLISHED],
    ['type', self::NODE_TYPE_HEARING],
  ];

  /**
   * Load hearings.
   */
  public function loadHearings(array $conditions = []) {
    $effectiveConditions = array_merge($this->defaultConditions, $conditions);
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->accessCheck(FALSE);
    foreach ($effectiveConditions as $condition) {
      $query->condition(...$condition);
    }
    $nids = $query->execute();

    return Node::loadMultiple($nids);
  }

  /**
   * Check if node is a hearing.
   */
  public function isHearing($node) {
    return !empty($node) && $node instanceof NodeInterface && self::NODE_TYPE_HEARING === $node->bundle();
  }

  /**
   * Get a date time object.
   */
  private function getDateTime($time = 'now', $timezone = 'UTC'): DrupalDateTime {
    return new DrupalDateTime($time, $timezone);
  }

  /**
   * Get number of replies.
   */
  public function getNumberOfReplies(NodeInterface $node): ?int {
    if (!$this->isHearing($node)) {
      return NULL;
    }

    return $this->deskproHearingHelper->getHearingTicketsCount($node);
  }

}
