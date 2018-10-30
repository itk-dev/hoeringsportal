<?php

namespace Drupal\hoeringsportal_config_settings\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\itk_admin\State\BaseConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Page controller.
 */
class PageController extends ControllerBase {
  /**
   * The configuration.
   *
   * @var \Drupal\itk_admin\State\BaseConfig
   */
  protected $config;

  /**
   * Constructs a new DeskproController object.
   */
  public function __construct(BaseConfig $config) {
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('itk_admin.itk_config')
    );
  }

  /**
   * Redirect to front page defined in configuration.
   */
  public function frontPage() {
    $frontpageId = $this->config->get('frontpage_id');
    $url = Url::fromRoute('entity.node.canonical', ['node' => $frontpageId ?? 1]);
    return new RedirectResponse($url->toString());
  }

  /**
   * Redirect to user's manual.
   */
  public function usersManual() {
    $url = $this->config->get('users_manual_url');
    if (empty($url)) {
      $this->messenger()->addError($this->t("No user's manual url defined!"));
      return $this->redirect('system.admin');
    }

    return new TrustedRedirectResponse($url);
  }

}
