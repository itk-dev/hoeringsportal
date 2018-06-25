<?php

namespace Drupal\itk_admin_links\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides admin links
 *
 * @Block(
 *   id = "itk_admin_links",
 *   admin_label = @Translation("ITK Admin links"),
 * )
 */
class ItkAdminLinks extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    $variables = array();
    $variables['nid'] = FALSE;
    if ($node) {
      $variables['nid'] = $node->id();
    }

    return array(
      '#type' => 'markup',
      '#theme' => 'itk_admin_links_block',
      '#attached' => array(
        'library' => array(
          'itk_admin_links/itk_admin_links',
        ),
      ),
      '#cache' => array(
        'max-age' => 0,
      ),
      '#nid' => $variables['nid'],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account, $return_as_object = FALSE) {
    return AccessResult::allowedIfHasPermission($account, 'access content overview');
  }
}
?>