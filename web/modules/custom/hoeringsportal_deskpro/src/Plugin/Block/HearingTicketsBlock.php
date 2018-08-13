<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Hearing tickets' Block.
 *
 * @Block(
 *   id = "hoeringsportal_deskpro_hearingtickets_block",
 *   admin_label = @Translation("Hearing tickets"),
 *   category = @Translation("Deskpro"),
 * )
 */
class HearingTicketsBlock extends BlockBase implements ContainerFactoryPluginInterface {
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
      $container->get('current_route_match'),
      $container->get('language_manager')
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
    LanguageManagerInterface $languageManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $routeMatch;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->routeMatch->getParameter('node');

    if (empty($node) || !($node instanceof NodeInterface) || 'hearing' !== $node->bundle()) {
      return NULL;
    }

    $contentUrl = Url::fromRoute(
      'hoeringsportal_deskpro.hearing.tickets',
      [
        'node' => $node->id(),
      ]
    )->toString();

    $configuration = [
      'container_id' => 'hearing-tickets-content',
      'content_url' => $contentUrl,
    ];

    return [
      '#theme' => 'hoeringsportal_hearing_tickets',
      '#node' => $node,
      '#is_loading' => TRUE,
      '#configuration' => $configuration,
      '#attached' => [
        'drupalSettings' => [
          'hearing_tickets' => $configuration,
        ],
        'library' => [
          'hoeringsportal_deskpro/hearing_tickets',
        ],
      ],
    ];
  }

}
