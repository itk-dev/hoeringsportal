<?php

namespace Drupal\hoeringsportal_public_meeting\Commands;

use Drupal\hoeringsportal_public_meeting\Helper\PublicMeetingHelper;
use Drush\Commands\DrushCommands as BaseDrushCommands;

/**
 * Custom drush commands from hoeringsportal_public_meeting.
 */
class DrushCommands extends BaseDrushCommands {
  /**
   * Public meetings helper.
   *
   * @var \Drupal\hoeringsportal_public_meeting\Helper\PublicMeetingHelper
   */
  private $helper;

  /**
   * Constructor.
   */
  public function __construct(PublicMeetingHelper $helper) {
    parent::__construct();
    $this->helper = $helper;
  }

  /**
   * Updates state on public meetings.
   *
   * @command hoeringsportal:public_meeting:state-update
   * @usage hoeringsportal:public_meeting:state-update
   *   Update state for all public meetings.
   */
  public function updatePublicMeetingState() {
    $meetings = $this->helper->loadPublicMeetings();
    foreach ($meetings as $meeting) {
      $newState = $this->helper->computeState($meeting);
      if ($this->helper->getState($meeting) !== $newState) {
        $this->helper->setState($meeting, $newState)->save();
        $this->writeln(json_encode([$meeting->id(), $newState]));
      }
    }
  }

  /**
   * Show public meetings states.
   *
   * @command hoeringsportal:public_meeting:state-show
   * @usage hoeringsportal:public_meeting:state-show
   *   Show state for all public meetings.
   */
  public function showPublicMeetingState() {
    $meetings = $this->helper->loadPublicMeetings();

    foreach ($meetings as $meeting) {
      $this->writeln(sprintf('%4d: %s', $meeting->id(), $this->helper->getState($meeting)));
    }
  }

}
