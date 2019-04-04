<?php

namespace Drupal\hoeringsportal_data\Plugin\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the MapConfiguration contraint.
 */
class MapConfigurationValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    header('Content-type: text/plain'); echo var_export([$value, get_class($constraint)], true); die(__FILE__.':'.__LINE__.':'.__METHOD__);
    // TODO: Implement validate() method.
  }
}
