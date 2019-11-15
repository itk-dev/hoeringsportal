<?php

namespace Drupal\hoeringsportal_data\Helper;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Hearing helper.
 */
class HearingHelper {
  const NODE_TYPE_HEARING = 'hearing';
  const STATE_UPCOMING = 'upcoming';
  const STATE_ACTIVE = 'active';
  const STATE_FINISHED = 'finished';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
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
  private function getDateTime($time = 'now', $timezone = 'UTC') {
    return new DrupalDateTime($time, $timezone);
  }

}
