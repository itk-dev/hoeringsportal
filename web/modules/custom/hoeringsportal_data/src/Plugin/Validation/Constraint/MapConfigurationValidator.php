<?php

namespace Drupal\hoeringsportal_data\Plugin\Validation\Constraint;

use Drupal\hoeringsportal_data\Exception\MapConfigurationParseException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the MapConfiguration contraint.
 */
class MapConfigurationValidator extends ConstraintValidator {
  /**
   * Map helper.
   *
   * @var \Drupal\hoeringsportal_data\Helper\MapHelper
   */
  private $helper;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    // @todo Use dependency injection for this.
    $this->helper = \Drupal::getContainer()->get('hoeringsportal_data.map_helper');
  }

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      try {
        $this->helper->validateConfiguration($item->value);
      }
      catch (MapConfigurationParseException $exception) {
        $previousException = $exception->getPrevious();
        if ($previousException) {
          $this->context->addViolation('Invalid map configuration: %message; %previous', [
            '%message' => $exception->getMessage(),
            '%previous' => $previousException->getMessage(),
          ]);
        }
        else {
          $this->context->addViolation('Invalid map configuration: %message', [
            '%message' => $exception->getMessage(),
          ]);
        }
      }
    }
  }

}
