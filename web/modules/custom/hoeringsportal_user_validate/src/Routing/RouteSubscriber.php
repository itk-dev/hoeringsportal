<?php

namespace Drupal\hoeringsportal_user_validate\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * @class
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   *
   * Alter the SAML endpoints to point to KOBA controller.
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Override login (Assertion Consumer Service).
    if ($route = $collection->get('samlauth.saml_controller_acs')) {
      $route->setDefaults(array(
        '_controller' => '\Drupal\hoeringsportal_user_validate\Controller\HoeringsportalUserValidateSAMLController::acs',
      ));
      $route->setRequirements(array(
        '_role' => 'authenticated',
      ));
    }

    if ($route = $collection->get('samlauth.saml_controller_login')) {
      $route->setRequirements(array(
        '_role' => 'authenticated',
      ));
    }
  }

}
