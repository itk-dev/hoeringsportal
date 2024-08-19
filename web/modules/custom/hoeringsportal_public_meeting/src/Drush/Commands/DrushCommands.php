<?php

namespace Drupal\hoeringsportal_public_meeting\Drush\Commands;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\State\StateInterface;
use Drupal\hoeringsportal_public_meeting\Helper\PublicMeetingHelper;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands as BaseDrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom drush commands for hoeringsportal_public_meeting.
 */
final class DrushCommands extends BaseDrushCommands {

  /**
   * Constructor.
   */
  public function __construct(
    private readonly PublicMeetingHelper $helper,
    private readonly TimeInterface $time,
    private readonly StateInterface $state,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_public_meeting.public_meeting_helper'),
      $container->get('datetime.time'),
      $container->get('state')
    );
  }

  /**
   * Updates state on public meetings.
   *
   * @command hoeringsportal:public_meeting:state-update
   * @usage hoeringsportal:public_meeting:state-update
   *   Update state for all public meetings.
   */
  #[CLI\Command(name: 'hoeringsportal:public_meeting:state-update')]
  public function updatePublicMeetingState() {
    $lastRunAt = $this->getLastRunAt(__METHOD__);
    $requestTime = $this->getRequestTime();

    $meetings = $this->helper->loadPublicMeetings();
    foreach ($meetings as $meeting) {
      $newState = $this->helper->computeState($meeting);
      if ($this->helper->getState($meeting) !== $newState) {
        $this->helper->setState($meeting, $newState)->save();
        $this->writeln(json_encode([$meeting->id(), $newState]));
      }

      // Clear cache if deadline has passed since last run.
      $deadline = $this->helper->getDeadline($meeting);
      if ($deadline) {
        // We need a DateTime for comparisons.
        $deadline = $deadline->getPhpDateTime();
        if ($lastRunAt <= $deadline && $deadline <= $requestTime) {
          $this->writeln(json_encode([$meeting->id(), 'Deadline passed']));
          $tags = $meeting->getCacheTags();
          Cache::invalidateTags($tags);
        }
      }
    }

    $this->setLastRunAt(__METHOD__);
  }

  /**
   * Show public meetings states.
   *
   * @command hoeringsportal:public_meeting:state-show
   * @usage hoeringsportal:public_meeting:state-show
   *   Show state for all public meetings.
   */
  #[CLI\Command(name: 'hoeringsportal:public_meeting:state-show')]
  public function showPublicMeetingState() {
    $meetings = $this->helper->loadPublicMeetings();

    foreach ($meetings as $meeting) {
      $this->writeln(sprintf('%4d: %s', $meeting->id(), $this->helper->getState($meeting)));
    }
  }

  /**
   * Get request time.
   */
  private function getRequestTime(): \DateTimeInterface {
    return new \DateTimeImmutable('@' . $this->time->getRequestTime());
  }

  /**
   * Get time of last run.
   */
  private function getLastRunAt(string $method): ?\DateTimeInterface {
    $time = $this->state->get($this->getLastRunKey($method));

    return new \DateTimeImmutable('@' . ($time ?? 0));
  }

  /**
   * Set time of last run.
   */
  private function setLastRunAt(string $method) {
    $this->state->set($this->getLastRunKey($method), $this->time->getRequestTime());
  }

  /**
   * Get last run key.
   */
  private function getLastRunKey(string $method): string {
    return $method . '_last_run_at';
  }

}
