<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Hearing ticket' Block.
 *
 * @Block(
 *   id = "hoeringsportal_deskpro_hearingticket_block",
 *   admin_label = @Translation("Hearing ticket"),
 *   category = @Translation("Deskpro"),
 * )
 */
class HearingTicketBlock extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

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
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RouteMatchInterface $routeMatch
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');
    $ticket = $this->routeMatch->getParameter('ticket');

    if ('hearing' !== $node->bundle() || empty($ticket)) {
      return NULL;
    }

    return [
      '#theme' => 'block__hoeringsportal_hearing_ticket',
      '#stuff' => __METHOD__,
    ];
  }

}
