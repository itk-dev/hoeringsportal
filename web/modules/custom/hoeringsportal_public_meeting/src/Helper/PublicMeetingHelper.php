<?php

namespace Drupal\hoeringsportal_public_meeting\Helper;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\hoeringsportal_public_meeting\Controller\PublicMeetingController;
use Drupal\itk_pretix\Plugin\Field\FieldType\PretixDate;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Public meeting helper.
 */
class PublicMeetingHelper {

  const NODE_TYPE_PUBLIC_MEETING = 'public_meeting';

  const STATE_UPCOMING = 'upcoming';

  const STATE_FINISHED = 'finished';

  /**
   * Constructor.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly RouteMatchInterface $routeMatch,
  ) {
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
  public function getDeadline(NodeInterface $node, bool $usePretixDates = TRUE): ?DrupalDateTime {
    if (!$this->isPublicMeeting($node)) {
      return NULL;
    }

    if ($usePretixDates && $this->hasPretixSignUp($node)) {
      $deadline = NULL;
      foreach ($this->getPretixDates($node) ?? [] as $date) {
        if (NULL === $deadline || $date->registration_deadline > $deadline) {
          $deadline = $date->registration_deadline;
        }
      }

      return $deadline;
    }
    else {
      return $node->field_registration_deadline->date;
    }
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
   * Check if a public meeting uses pretix signup.
   */
  public function hasPretixSignUp(NodeInterface $node) {
    if (!$this->isPublicMeeting($node)) {
      return FALSE;
    }

    return 'pretix' === $node->field_signup_selection->value;
  }

  /**
   * Check if a public meeting has been held.
   */
  public function hasBeenHeld(NodeInterface $node) {
    if (!$this->isPublicMeeting($node)) {
      return FALSE;
    }

    return $this->hasPretixSignUp($node)
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
    ['type', self::NODE_TYPE_PUBLIC_MEETING],
  ];

  /**
   * Load public_meetings.
   */
  public function loadPublicMeetings(array $conditions = []) {
    $effectiveConditions = array_merge($this->defaultConditions, $conditions);
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->accessCheck();
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
    return !empty($node) && $node instanceof NodeInterface && self::NODE_TYPE_PUBLIC_MEETING === $node->bundle();
  }

  /**
   * Get a date time object.
   */
  private function getDateTime($time = 'now', $timezone = 'UTC') {
    return new DrupalDateTime($time, $timezone);
  }

  /**
   * Implements hook_preprocess_HOOK().
   *
   * Adds context on public meetings if any.
   */
  public function preprocess(array &$variables, string $hook): void {
    $node = $variables['node'] ?? NULL;
    if (NULL === $node && 'field_pretix_dates' === ($variables['field_name'] ?? NULL)) {
      $node = $this->routeMatch->getParameter('node');
    }

    if ($this->isPublicMeeting($node) && $context = $this->getPublicMeetingContext($node)) {
      $variables['public_meeting_context'] = $context;
    }
  }

  /**
   * Get public meeting context.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The public meeting.
   *
   * @return array
   *   Info on the context
   *     current: The current date if any
   *     previous: The previous date (if current is set)
   *     next: The next data (if current is set)
   *     upcoming: Dates after now (sorted by start time)
   */
  public function getPublicMeetingContext(NodeInterface $node): ?array {
    if (!$this->isPublicMeeting($node)) {
      return NULL;
    }

    $datesDelta = -1;
    $routeNode = $this->routeMatch->getParameter('node');
    if ($this->isPublicMeeting($routeNode) || $node === $routeNode) {
      $datesDelta = (int) ($this->routeMatch->getParameter(PublicMeetingController::DATES_DELTA) ?? -1);
    }

    /** @var \Drupal\itk_pretix\Plugin\Field\FieldType\PretixDate[] $dates */
    $dates = iterator_to_array($node->get('field_pretix_dates')->getIterator());
    // Sort dates by time_from.
    usort($dates, static fn(PretixDate $a, PretixDate $b) => $a->get('time_from')->getValue() <=> $b->get('time_from')->getValue());

    $previous = NULL;
    $current = NULL;
    $next = NULL;

    if ($datesDelta > -1) {
      foreach ($dates as $index => $date) {
        if ($datesDelta === (int) $date->getName()) {
          $current = $date;
          $previous = $dates[$index - 1] ?? NULL;
          $next = $dates[$index + 1] ?? NULL;
          break;
        }
      }
    }

    $now = new DrupalDateTime();
    $upcoming = array_values(array_filter($dates, static fn(PretixDate $date) => $date->get('time_from')->getValue() > $now));

    return array_filter([
      'previous' => $previous,
      'current' => $current,
      'next' => $next,
      'upcoming' => $upcoming,
    ]);
  }

  /**
   * Check if public meeting has pretix date ending between two times.
   */
  public function hasDateEndingBetween(NodeInterface $node, ?\DateTimeInterface $from, \DateTimeInterface $to): bool {
    if (!$this->isPublicMeeting($node) || !$this->hasPretixSignUp($node)) {
      return FALSE;
    }

    $from ??= new \DateTimeImmutable('@0');

    if ($dates = $this->getPretixDates($node)) {
      foreach ($dates as $date) {
        if ($from <= $date->time_to && $date->time_to <= $to) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * Get pretix dates (if any) from a public meeting.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The public meeting.
   *
   * @return \Drupal\itk_pretix\Plugin\Field\FieldType\PretixDate[]|iterable|null
   *   The pretix dates if any.
   */
  private function getPretixDates(NodeInterface $node): ?iterable {
    if (!$this->isPublicMeeting($node) || !$this->hasPretixSignUp($node)) {
      return NULL;
    }

    return $node->get('field_pretix_dates');
  }

}
