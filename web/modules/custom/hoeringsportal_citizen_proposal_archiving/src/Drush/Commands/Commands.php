<?php

namespace Drupal\hoeringsportal_citizen_proposal_archiving\Drush\Commands;

use Drupal\hoeringsportal_citizen_proposal\Helper\Helper as CitizenProposalHelper;
use Drupal\hoeringsportal_citizen_proposal_archiving\Helper\Helper;
use Drupal\hoeringsportal_citizen_proposal_archiving\Renderer\Renderer;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush commands.
 */
final class Commands extends DrushCommands {

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(Helper::class),
      $container->get(CitizenProposalHelper::class),
      $container->get(Renderer::class)
    );
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
