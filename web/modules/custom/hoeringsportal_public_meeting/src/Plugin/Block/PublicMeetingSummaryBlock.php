<?php

namespace Drupal\hoeringsportal_public_meeting\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\hoeringsportal_public_meeting\Helper\PublicMeetingHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Public meeting summary Block.
 *
 * @Block(
 *   id = "hoeringsportal_public_meeting_summary_block",
 *   admin_label = @Translation("Public meeting summary"),
 *   category = @Translation("Public meeting"),
 * )
 */
final class PublicMeetingSummaryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Block constructor.
   *
   * @param array $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   Definition of plugin.
   * @param \Drupal\hoeringsportal_public_meeting\Helper\PublicMeetingHelper $helper
   *   The public meeting helper.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly PublicMeetingHelper $helper,
    private readonly RouteMatchInterface $routeMatch,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Create function for block.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   Definition of plugin.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('hoeringsportal_public_meeting.public_meeting_helper'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');
    if (!$this->helper->isPublicMeeting($node)) {
      return [];
    }

    $signupDeadline = NULL;
    if ($this->helper->showRegistrationDeadline($node) && !$this->helper->hasPretixSignUp($node)) {
      $signupDeadline = $node->field_registration_deadline[0]?->getValue();
    }
    $cacheTags = $node->getCacheTags();

    $cacheContexts = $node->getCacheContexts();
    $cacheContexts[] = 'url';
    $cacheContexts = array_unique($cacheContexts);

    $context = $this->helper->getPublicMeetingContext($node);

    return [
      '#theme' => 'hoeringsportal_public_meeting_summary',
      '#pretix_signup' => $context['upcoming'][0] ?? NULL,
      '#signup_deadline' => $signupDeadline,
      '#node' => $node,
      '#cache' => [
        'contexts' => $cacheContexts,
        'tags' => $cacheTags,
      ],
    ];
  }

}
