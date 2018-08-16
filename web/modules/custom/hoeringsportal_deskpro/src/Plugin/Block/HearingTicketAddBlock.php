<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

/**
 * Provides a 'Hearing ticket' Block.
 *
 * @Block(
 *   id = "hoeringsportal_deskpro_hearingticket_add_block",
 *   admin_label = @Translation("Hearing ticket add"),
 *   category = @Translation("Deskpro"),
 * )
 */
class HearingTicketAddBlock extends HearingTicketBlock {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');

    if (!$this->helper->isHearing($node)) {
      return NULL;
    }

    if ($this->helper->isDeadlinePassed($node)) {
      return NULL;
    }

    $departmentId = $this->helper->getDepartmentId($node);
    $hearingId = $this->helper->getHearingId($node);
    $defaultValues = [];

    $form = $this->deskpro->getTicketEmbedForm($departmentId, $hearingId, $defaultValues);

    return [
      '#children' => $form,
      '#cache' => ['contexts' => ['url']],
    ];
  }

}
