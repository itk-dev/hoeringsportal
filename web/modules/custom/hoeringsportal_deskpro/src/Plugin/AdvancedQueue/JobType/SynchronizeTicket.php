<?php

namespace Drupal\hoeringsportal_deskpro\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Synchronize hearing job.
 *
 * @AdvancedQueueJobType(
 *   id = "Drupal\hoeringsportal_deskpro\Plugin\AdvancedQueue\JobType\SynchronizeTicket",
 *   label = @Translation("Update or create ticket"),
 * )
 */
final class SynchronizeTicket extends JobTypeBase implements ContainerFactoryPluginInterface {
  /**
   * The hearing helper.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\HearingHelper
   */
  private $helper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
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
    HearingHelper $helper,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   */
  public function process(Job $job) {
    $payload = $job->getPayload();

    try {
      $this->helper->runSynchronizeTicket($payload);

      return JobResult::success();
    }
    catch (\Exception $exception) {
      return JobResult::failure($exception->getMessage());
    }
  }

}
