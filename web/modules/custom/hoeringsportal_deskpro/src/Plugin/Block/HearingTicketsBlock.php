<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
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
    if ('hearing' !== $node->bundle()) {
      return NULL;
    }

    $data_url = Url::fromRoute('hoeringsportal_deskpro.api_controller_hearings_tickets', ['hearing' => $node->field_hearing_id->value])->toString();
    $ticket_add_url = Url::fromRoute('hoeringsportal_deskpro.hearing.ticket_add', ['node' => $node->id()])->toString();
    $ticket_view_url = str_replace(urlencode('{ticket}'), '{ticket}', Url::fromRoute('hoeringsportal_deskpro.hearing.ticket_view', ['node' => $node->id(), 'ticket' => '{ticket}'])->toString());

    return [
      'content' => [
        '#markup' => '<div id="hearing-tickets-content"></div>',
      ],
      'configuration' => [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#value' => json_encode([
          'data_url' => $data_url,
          'ticket_add_url' => $ticket_add_url,
          'ticket_view_url' => $ticket_view_url,
          'locale' => $this->languageManager->getDefaultLanguage()->getId(),
        ]),
        '#attributes' => [
          'id' => 'hearing-tickets-config',
          'type' => 'application/json',
        ],
      ],
      '#attached' => ['library' => ['hoeringsportal_deskpro/hearing_tickets']],
    ];
  }

}
