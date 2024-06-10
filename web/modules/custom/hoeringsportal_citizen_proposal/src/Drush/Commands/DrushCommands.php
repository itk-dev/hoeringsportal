<?php

namespace Drupal\hoeringsportal_citizen_proposal\Drush\Commands;

use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands as BaseDrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom drush commands for citizen proposal.
 */
final class DrushCommands extends BaseDrushCommands {

  /**
   * Constructor for the citizen proposal commands class.
   */
  public function __construct(
    readonly private Helper $helper,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(Helper::class),
    );
  }

  /**
   * A drush command for finishing a specific proposal.
   */
  #[CLI\Command(name: 'hoeringsportal-citizen-proposal:finish-proposal')]
  #[CLI\Argument(name: 'proposalId', description: 'The proposal (node) id to finish')]
  public function finishProposal(int $proposalId): void {
    $this->helper->finishProposal($proposalId);
  }

  /**
   * A drush command for finishing all overdue proposals.
   */
  #[CLI\Command(name: 'hoeringsportal-citizen-proposal:finish-overdue-proposals')]
  public function finishOverdueProposals(): void {
    $overdueProposals = $this->helper->findOverdueProposals();

    foreach ($overdueProposals as $proposalId) {
      $this->helper->finishProposal($proposalId);
    }
  }

}
