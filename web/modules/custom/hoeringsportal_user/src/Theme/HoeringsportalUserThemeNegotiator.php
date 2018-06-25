<?php
/**
 * @file
 * Contains \Drupal\hoeringsportal_user\Theme\HoeringsportalUserThemeNegotiator.
 */
namespace Drupal\hoeringsportal_user\Theme;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
/**
 * Class ThemeNegotiator
 *
 * Controls which theme is applies to a given path.
 *
 * @package Drupal\hoeringsportal_user\Theme
 */
class HoeringsportalUserThemeNegotiator implements ThemeNegotiatorInterface {
  /**
   * Renegotiate paths from administration theme to hoeringsportal theme.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route
   * @return bool
   */
  public function applies(RouteMatchInterface $route) {
    switch ($route->getRouteName()) {
      case 'entity.user.edit_form':
        return true;
      case 'entity.user.cancel_form':
        return true;
    }
    return false;
  }
  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route) {
     return 'hoeringsportal';
  }
}
