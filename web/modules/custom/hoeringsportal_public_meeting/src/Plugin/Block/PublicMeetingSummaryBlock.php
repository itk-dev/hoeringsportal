<?php

namespace Drupal\hoeringsportal_public_meeting\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\hoeringsportal_public_meeting\Helper\PublicMeetingHelper;

/**
 * Provides a Public meeting summary Block.
 *
 * @Block(
 *   id = "hoeringsportal_public_meeting_summary_block",
 *   admin_label = @Translation("Public meeting summary"),
 *   category = @Translation("Public meeting"),
 * )
 */
class PublicMeetingSummaryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Helper class for public meetings.
   *
   * @var \Drupal\hoeringsportal_public_meeting\Helper\PublicMeetingHelper
   */
  protected $helper;

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
   *   Helper class.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PublicMeetingHelper $helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->helper = $helper;
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
      $container->get('hoeringsportal_public_meeting.public_meeting_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');

    if (!$this->helper->isPublicMeeting($node)) {
      return [];
    }
    $showRegistrationDeadline = $this->helper->showRegistrationDeadline($node);

    $cacheTags = $node->getCacheTags();

    $cacheContexts = $node->getCacheContexts();
    $cacheContexts[] = 'url';
    $cacheContexts = array_unique($cacheContexts);

    return [
      '#theme' => 'hoeringsportal_public_meeting_summary',
      '#pretix_signup' => isset($node->field_pretix_dates[0]) ? $node->field_pretix_dates[0]->getValue() : NULL,
      '#signup_deadline' => ($showRegistrationDeadline && isset($node->field_registration_deadline[0])) ? $node->field_registration_deadline[0]->getValue() : NULL,
      '#node' => $node,
      '#cache' => [
        'contexts' => $cacheContexts,
        'tags' => $cacheTags,
      ],
    ];
  }

}
