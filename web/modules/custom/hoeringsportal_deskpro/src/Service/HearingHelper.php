<?php

namespace Drupal\hoeringsportal_deskpro\Service;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\NodeInterface;

/**
 * Hearing helper.
 */
class HearingHelper {

  /**
   * Check if hearing deadline is passed.
   */
  public function isDeadlinePassed(NodeInterface $node) {
    if ('hearing' !== $node->bundle()) {
      return FALSE;
    }

    $deadline = $node->field_reply_deadline->value;

    if (empty($deadline)) {
      return FALSE;
    }

    return new DrupalDateTime() > new DrupalDateTime($deadline);
  }

  /**
   * Check if node is a hearing.
   */
  public function isHearing($node) {
    return !empty($node) && $node instanceof NodeInterface && 'hearing' === $node->bundle();
  }

}
