<?php

namespace Drupal\hoeringsportal_data\Drush\Commands;

use Drupal\hoeringsportal_data\Helper\HearingHelper;
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
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_data.hearing_helper'),
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

}
