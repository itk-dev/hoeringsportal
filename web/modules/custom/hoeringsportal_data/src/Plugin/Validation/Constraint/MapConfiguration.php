<?php

namespace Drupal\hoeringsportal_data\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is a valid map configuration.
 *
 * @Constraint(
 *   id = "MapConfiguration",
 *   label = @Translation("Map configuration", context = "Validation"),
 *   type = "string"
 * )
 */
class MapConfiguration extends Constraint {

}
