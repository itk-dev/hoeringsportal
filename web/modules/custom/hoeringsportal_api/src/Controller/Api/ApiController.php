<?php

namespace Drupal\hoeringsportal_api\Controller\Api;

use Drupal\Core\Controller\ControllerBase;
use Drupal\hoeringsportal_api\Service\Helper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
abstract class ApiController extends ControllerBase {
  /**
   * @var \Drupal\hoeringsportal_api\Service\Helper*/
  private $helper;

  /**
   *
   */
  public function __construct(Helper $hearingHelper) {
    $this->helper = $hearingHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_api.helper')
    );
  }

  /**
   *
   */
  protected function helper() {
    return $this->helper;
  }

  /**
   *
   */
  protected function generateUrl($name, $parameters = [], $options = []) {
    return $this->helper()->generateUrl($name, $parameters, $options);
  }

}
