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

    if ($node === NULL || 'hearing' !== $node->bundle()) {
      return NULL;
    }

    $departmentId = $node->field_deskpro_department_id->value;
    $hearingId = $node->field_deskpro_hearing_id->value;
    $defaultValues = [];

    $form = $this->deskpro->getTicketEmbedForm($departmentId, $hearingId, $defaultValues);

    return [
      '#children' => $form,
    ];
  }

}
