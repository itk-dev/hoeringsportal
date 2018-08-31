<?php

namespace Drupal\hoeringsportal_user_validate\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 *
 * @class
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
      $route->setDefaults([
        '_controller' => '\Drupal\hoeringsportal_user_validate\Controller\HoeringsportalUserValidateSAMLController::acs',
      ]);
      $route->setRequirements([
        '_role' => 'authenticated',
      ]);
    }

    if ($route = $collection->get('samlauth.saml_controller_login')) {
      $route->setRequirements([
        '_role' => 'authenticated',
      ]);
    }
  }

}
