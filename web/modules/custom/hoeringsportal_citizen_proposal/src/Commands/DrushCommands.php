<?php

namespace Drupal\hoeringsportal_citizen_proposal\Commands;

use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;
use Drush\Commands\DrushCommands as BaseDrushCommands;

/**
 * Custom drush commands for citizen proposal.
 */
class DrushCommands extends BaseDrushCommands {

  /**
   * Constructor for the citizen proposal commands class.
   */
  public function __construct(
    readonly private Helper $helper
  ) {
    parent::__construct();
  }

  /**
   * A drush command for finishing a specific proposal.
   *
   * @param int $proposalId
   *   A node id to finish.
   *
   * @command hoeringsportal-citizen-proposal:finish-proposal
   *
   * @aliases hcp-fp
   */
  public function finishProposal(int $proposalId): void {
    $this->helper->finishProposal($proposalId);
  }

  /**
   * A drush command for finishing all overdue proposals.
   *
   * @command hoeringsportal-citizen-proposal:finish-overdue-proposals
   *
   * @aliases hcp-fop
   */
  public function finishOverdueProposals(): void {
    $overdueProposals = $this->helper->findOverdueProposals();

    foreach ($overdueProposals as $proposalId) {
      $this->helper->finishProposal($proposalId);
    }
  }

}
