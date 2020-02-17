<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted agent email is valid.
 *
 * @Constraint(
 *   id = "AgentEmail",
 *   label = @Translation("Agent email", context = "Validation"),
 * )
 */
class AgentEmailContraint extends Constraint {

  /**
   * The message that will be shown if the format is incorrect.
   *
   * @var string
   */
  public $invalidAgentEmail = 'Invalid agent email: %email.';

}
