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
   * Get end time for a public meeting.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|null
   *   The end time.
   */
  public function getEndTime(NodeInterface $public_meeting) {
    if (!$this->isPublicMeeting($public_meeting)) {
      return NULL;
    }

    return $public_meeting->field_last_meeting_time->date;
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
    $endTime = $this->getEndTime($public_meeting);
    if (empty($endTime) || $endTime >= $now) {
      return self::STATE_UPCOMING;
    }

    return self::STATE_FINISHED;
  }

  /**
   * Get deadline for a public_meeting.
   */
  public function getDeadline(NodeInterface $node) {
    if (!$this->isPublicMeeting($node)) {
      return FALSE;
    }

    return $node->field_registration_deadline->date;
  }

  /**
   * Check if public_meeting deadline is passed.
   */
  public function isDeadlinePassed(NodeInterface $node) {
    if (!$this->isPublicMeeting($node)) {
      return FALSE;
    }

    $deadline = $this->getDeadline($node);

    return $deadline && $this->getDateTime() > $deadline;
  }

  /**
   * Check if a public meeting has been cancelled.
   */
  public function isCancelled(NodeInterface $node) {
    if (!$this->isPublicMeeting($node)) {
      return FALSE;
    }

    return 1 === (int) $node->field_public_meeting_cancelled->value;
  }

  /**
   * Decide if the registration deadline must be showed.
   */
  public function showRegistrationDeadline(NodeInterface $node) {
    if (!$this->isPublicMeeting($node)) {
      return FALSE;
    }

    return 0 === (int) $node->field_hidden_signup->value;
  }

  /**
   * Check if a public meeting has been held.
   */
  public function hasBeenHeld(NodeInterface $node) {
    if (!$this->isPublicMeeting($node)) {
      return FALSE;
    }

    return 'pretix' === $node->field_signup_selection->value
      ? $this->hasBeenHeldPretix($node)
      : $this->hasBeenHeldManual($node);
  }

  /**
   * Decide if a meeting with pretix sign up has been held.
   *
   * @param \Drupal\node\Entity\NodeInterface $node
   *   The node.
   *
   * @return bool
   *   False iff one date has an end time in the future.
   */
  private function hasBeenHeldPretix(NodeInterface $node) {
    /** @var \Drupal\itk_pretix\Plugin\Field\FieldType\PretixDate $date */
    $now = new DrupalDateTime();
    foreach ($node->field_pretix_dates as $date) {
      if ($date->time_to > $now) {
        return FALSE;
      }
    }

    // No date has end time in the future.
    return TRUE;
  }

  /**
   * Decide if a meeting with manual sign up has been held.
   *
   * @param \Drupal\node\Entity\NodeInterface $node
   *   The node.
   *
   * @return bool
   *   False iff the meetings meeting time is not in the future.
   */
  private function hasBeenHeldManual(NodeInterface $node) {
    $now = new DrupalDateTime();

    return $node->field_last_meeting_time->value <= $now;
  }

  /**
   * Get pretix sign up url from data on Pretix date item.
   *
   * @param array $data
   *   The data.
   *
   * @return string|null
   *   The pretix sign up url if any.
   */
  public function getPretixSignUpUrl(array $data) {
    return $data['data']['pretix_subevent_shop_url'] ?? NULL;
  }

  /**
   * Get pretix availabilty from data on Pretix date item.
   *
   * @param array $data
   *   The data.
   *
   * @return string|null
   *   The pretix availability if any.
   */
  public function getPretixAvailability(array $data) {
    $availability = $data['data']['availability'] ?? [];
    if (!is_array($availability)) {
      return NULL;
    }

    return reset($availability)['availability'] ?? NULL;
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
