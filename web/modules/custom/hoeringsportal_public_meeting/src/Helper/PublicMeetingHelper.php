<?php

namespace Drupal\hoeringsportal_public_meeting\Helper;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Public meeting helper.
 */
class PublicMeetingHelper {
  const NODE_TYPE_HEARING = 'public_meeting';
  const STATE_UPCOMING = 'upcoming';
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
   * Get current public_meeting state.
   */
  public function getState(NodeInterface $public_meeting) {
    if (!$this->isPublicMeeting($public_meeting)) {
      return NULL;
    }

    return $public_meeting->field_content_state->value;
  }

  /**
   * Set public_meeting state.
   */
  public function setState(NodeInterface $public_meeting, $state) {
    $public_meeting->field_content_state->value = $state;

    return $public_meeting;
  }

  /**
   * Compute public_meeting state.
   */
  public function computeState(NodeInterface $public_meeting) {
    if (!$this->isPublicMeeting($public_meeting)) {
      return NULL;
    }

    $now = $this->getDateTime();
    $endTime = $public_meeting->field_last_meeting_time->date;
    print $now;
    if (empty($endTime) || $endTime >= $now) {
      return self::STATE_UPCOMING;
    }

    if (!empty($endTime) && $endTime < $now) {
      return self::STATE_FINISHED;
    }
  }

  /**
   * Check if public_meeting deadline is passed.
   */
  public function isDeadlinePassed(NodeInterface $node) {
    if (!$this->isPublicMeeting($node)) {
      return FALSE;
    }

    $deadline = $node->field_registration_deadline->date;

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
   * Load public_meetings.
   */
  public function loadPublicMeetings(array $conditions = []) {
    $effectiveConditions = array_merge($this->defaultConditions, $conditions);
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    foreach ($effectiveConditions as $condition) {
      $query->condition(...$condition);
    }
    $nids = $query->execute();

    return Node::loadMultiple($nids);
  }

  /**
   * Check if node is a public_meeting.
   */
  public function isPublicMeeting($node) {
    return !empty($node) && $node instanceof NodeInterface && self::NODE_TYPE_HEARING === $node->bundle();
  }

  /**
   * Get a date time object.
   */
  private function getDateTime($time = 'now', $timezone = 'UTC') {
    return new DrupalDateTime($time, $timezone);
  }

}
