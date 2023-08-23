<?php

namespace Drupal\hoeringsportal_citizen_proposal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;

/**
 * Controller for citizen proposals.
 */
class Controller extends ControllerBase {

  /**
   * Render view of citizen proposal supporters.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The citizen proposal.
   *
   * @return array
   *   The view renderable array.
   */
  public function supporters(NodeInterface $node): array {
    return views_embed_view('citizen_proposal_support', 'page_1', $node->id()) ?: [];
  }

}
