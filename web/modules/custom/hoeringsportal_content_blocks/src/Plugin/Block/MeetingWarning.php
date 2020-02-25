<?php

namespace Drupal\hoeringsportal_content_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides meeting warning block content.
 *
 * @Block(
 *   id = "meeting_warning",
 *   admin_label = @Translation("Node public meeting warning"),
 * )
 */
class MeetingWarning extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The node the block appears on.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentNode;

  /**
   * Block constructor.
   *
   * @param array $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   Block plugin id.
   * @param mixed $plugin_definition
   *   Block plugin definition.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $routeMatch
   *   The route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentRouteMatch $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentNode = $routeMatch->getParameter('node');
  }

  /**
   * Create block.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container interface.
   * @param array $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   Block plugin id.
   * @param mixed $plugin_definition
   *   Block plugin definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
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
  public function build() {
    if (isset($this->currentNode) && $this->currentNode->field_content_state->value) {
      $config['content_state'] = $this->currentNode->field_content_state->value;
      return [
        '#type' => 'markup',
        '#theme' => 'hoeringsportal_meeting_warning',
        '#config' => $config,
      ];
    }
    return NULL;
  }

}
