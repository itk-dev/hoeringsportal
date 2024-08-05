<?php

namespace Drupal\hoeringsportal_citizen_proposal_archiving\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\hoeringsportal_citizen_proposal_archiving\Helper\Helper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Archive citizen proposal.
 *
 * @AdvancedQueueJobType(
 *   id = "Drupal\hoeringsportal_citizen_proposal_archiving\Plugin\AdvancedQueue\JobType\ArchiveCitizenProposalJob",
 *   label = @Translation("Archive citizen proposal"),
 *   max_retries = 5,
 *   retry_delay = 60,
 * )
 */
final class ArchiveCitizenProposalJob extends JobTypeBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get(Helper::class)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    readonly private Helper $helper,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function process(Job $job): JobResult {
    return $this->helper->processJob($job);
  }

}
