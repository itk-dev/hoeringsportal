<?php

namespace Drupal\hoeringsportal_data\Commands;

use Drupal\hoeringsportal_data\Helper\HearingHelper;
use Drush\Commands\DrushCommands as BaseDrushCommands;

/**
 * Custom drush commands from hoeringsportal_deskpro.
 */
class DrushCommands extends BaseDrushCommands {
  /**
   * Deskpro helper.
   *
   * @var \Drupal\hoeringsportal_data\Helper\HearingHelper
   */
  private $helper;

  /**
   * Constructor.
   */
  public function __construct(HearingHelper $helper) {
    parent::__construct();
    $this->helper = $helper;
  }

  /**
   * Updates state on hearings.
   *
   * @command hoeringsportal:data:hearing-state-update
   * @usage hoeringsportal:data:hearing-state-update
   *   Update state for all hearings.
   */
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
  public function showHearingState() {
    $hearings = $this->helper->loadHearings();

    foreach ($hearings as $hearing) {
      $this->writeln(sprintf('%4d: %s', $hearing->id(), $this->helper->getState($hearing)));
    }
  }

}
