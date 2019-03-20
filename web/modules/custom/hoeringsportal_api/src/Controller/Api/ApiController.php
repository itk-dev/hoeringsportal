<?php

namespace Drupal\hoeringsportal_api\Controller\Api;

use Drupal\Core\Controller\ControllerBase;

/**
 *
 */
abstract class ApiController extends ControllerBase {

  /**
   *
   */
  protected function generateUrl($name, $parameters = [], $options = []) {
    $options += [
      'absolute' => TRUE,
    ];

    return $this->getUrlGenerator()->generateFromRoute($name, $parameters, $options);
  }

}
