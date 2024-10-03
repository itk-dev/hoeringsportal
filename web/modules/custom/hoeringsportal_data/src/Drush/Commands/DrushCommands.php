<?php

namespace Drupal\hoeringsportal_data\Drush\Commands;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\State\StateInterface;
use Drupal\hoeringsportal_data\Helper\HearingHelper;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper as DeskproHearingHelper;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands as BaseDrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom drush commands for hoeringsportal_data.
 */
final class DrushCommands extends BaseDrushCommands {

  /**
   * Constructor.
   */
  public function __construct(
    private readonly HearingHelper $helper,
    private readonly DeskproHearingHelper $deskproHelper,
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
      $container->get('hoeringsportal_data.hearing_helper'),
      $container->get('hoeringsportal_deskpro.helper'),
      $container->get('datetime.time'),
      $container->get('state')
    );
  }

  /**
   * Updates state on hearings.
   *
   * @command hoeringsportal:data:hearing-state-update
   * @usage hoeringsportal:data:hearing-state-update
   *   Update state for all hearings.
   */
  #[CLI\Command(name: 'hoeringsportal:data:hearing-state-update')]
  public function updateHearingState() {
    $hearings = $this->helper->loadHearings();

    foreach ($hearings as $hearing) {
      $newState = $this->helper->computeState($hearing);
      if ($this->helper->getState($hearing) !== $newState) {
        $this->helper->setState($hearing, $newState)->save();
        $this->writeln(json_encode([$hearing->id(), $newState]));
      }
    }
  }

  /**
   * Show hearings states.
   *
   * @command hoeringsportal:data:hearing-state-show
   * @usage hoeringsportal:data:hearing-state-show
   *   SHow state for all hearings.
   */
  #[CLI\Command(name: 'hoeringsportal:data:hearing-state-show')]
  public function showHearingState() {
    $hearings = $this->helper->loadHearings();

    foreach ($hearings as $hearing) {
      $this->writeln(sprintf('%4d: %s', $hearing->id(), $this->helper->getState($hearing)));
    }
  }

  /**
   * A drush command for deleting replies from hearings.
   */
  #[CLI\Command(name: 'hoeringsportal:data:delete-replies')]
  public function processDeleteHearingReplies(
    array $options = [
      'last-run-at' => NULL,
    ],
  ): void {
    $lastRunAt = $options['last-run-at'] ? new DrupalDateTime($options['last-run-at']) : $this->getLastRunAt(__METHOD__);
    $requestTime = $this->getRequestTime();

    $hearingIds = $this->helper->findHearingWhoseRepliesMustBeDeleted($lastRunAt,
      $requestTime);

    if ($this->io()->isVerbose()) {
      $this->io()->info(sprintf('Deleting hearing replies between %s and %s: %s', $lastRunAt->format('Y-m-d'), $requestTime->format('Y-m-d'), implode(', ', $hearingIds)));
    }

    if ($options['yes'] || $this->confirm(sprintf('Delete replies on hearings %s', implode(', ', $hearingIds)))) {
      $result = $this->deskproHelper->deleteHearingReplies($hearingIds);

      $this->io()->success(1 === $result
        ? sprintf('Replies deleted from 1 hearing: %s', implode(', ', $hearingIds))
        : sprintf('Replies deleted from %d hearings: %s', $result, implode(', ', $hearingIds))
      );

      $this->setLastRunAt(__METHOD__);
    }
  }

  /**
   * Get request time.
   */
  private function getRequestTime(): DrupalDateTime {
    return DrupalDateTime::createFromTimestamp($this->time->getRequestTime());
  }

  /**
   * Get time of last run.
   */
  private function getLastRunAt(string $method): DrupalDateTime {
    $time = $this->state->get($this->getLastRunKey($method));

    return DrupalDateTime::createFromTimestamp($time ?? 0);
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
