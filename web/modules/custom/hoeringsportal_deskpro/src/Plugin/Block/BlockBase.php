<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

use Drupal\Core\Block\BlockBase as BaseBlockBase;
use Drupal\Core\Cache\UncacheableDependencyTrait;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base block.
 */
abstract class BlockBase extends BaseBlockBase implements ContainerFactoryPluginInterface {
  use UncacheableDependencyTrait;

  /**
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The Deskpro service.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\DeskproService
   */
  protected $deskpro;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The hearing helper.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\HearingHelper
   */
  protected $helper;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('language_manager'),
      $container->get('hoeringsportal_deskpro.deskpro'),
      $container->get('hoeringsportal_deskpro.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $routeMatch,
    LanguageManagerInterface $languageManager,
    DeskproService $deskpro,
    HearingHelper $helper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $routeMatch;
    $this->languageManager = $languageManager;
    $this->deskpro = $deskpro;
    $this->helper = $helper;
  }

}
