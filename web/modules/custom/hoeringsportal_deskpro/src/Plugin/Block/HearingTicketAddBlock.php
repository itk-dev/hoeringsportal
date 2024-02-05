<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

use Drupal\hoeringsportal_deskpro\Form\HearingTicketAddForm;

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
    if ($this->helper->isDeadlinePassed($node)) {
      return [];
    }

    $form = \Drupal::formBuilder()->getForm(HearingTicketAddForm::class, $node);

    return $form;
  }

}
