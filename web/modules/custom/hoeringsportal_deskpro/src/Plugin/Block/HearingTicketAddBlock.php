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
    $form = \Drupal::formBuilder()->getForm('Drupal\hoeringsportal_deskpro\Form\HearingAddForm');
    return $form;
  }

}
