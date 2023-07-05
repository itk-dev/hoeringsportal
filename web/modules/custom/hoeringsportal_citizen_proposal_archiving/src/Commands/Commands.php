<?php

namespace Drupal\hoeringsportal_citizen_proposal_archiving\Commands;

use Drupal\hoeringsportal_citizen_proposal\Helper\Helper as CitizenProposalHelper;
use Drupal\hoeringsportal_citizen_proposal_archiving\Helper\Helper;
use Drupal\hoeringsportal_citizen_proposal_archiving\Renderer\Renderer;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Drush commands.
 */
class Commands extends DrushCommands {

  /**
   * Constructor.
   */
  public function __construct(
    readonly private Helper $helper,
    readonly private CitizenProposalHelper $citizenProposalHelper,
    readonly private Renderer $renderer
  ) {
  }

  /**
   * Render node.
   */
  #[CLI\Command(name: 'hoeringsportal_citizen_proposal_archiving:render')]
  #[CLI\Argument(name: 'nid', description: 'Node id')]
  #[CLI\Option(name: 'format', description: 'Output format', suggestedValues: ['pdf'])]
  #[CLI\Usage(name: 'drush hoeringsportal_citizen_proposal_archiving:render 87 --format=html', description: 'Render node 87 as HTML')]
  public function render($nid, $options = ['format' => 'pdf']) {
    $node = $this->citizenProposalHelper->loadCitizenProposal($nid);

    echo $this->helper->renderPdf($node);
  }

}
