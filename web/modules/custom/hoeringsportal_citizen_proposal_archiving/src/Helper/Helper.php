<?php

namespace Drupal\hoeringsportal_citizen_proposal_archiving\Helper;

use Drupal\advancedqueue\Entity\QueueInterface;
use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entity_events\EntityEventType;
use Drupal\entity_events\Event\EntityEvent;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper as CitizenProposalHelper;
use Drupal\hoeringsportal_citizen_proposal_archiving\Archiver\AbstractArchiver;
use Drupal\hoeringsportal_citizen_proposal_archiving\Exception\RuntimeException;
use Drupal\hoeringsportal_citizen_proposal_archiving\Plugin\AdvancedQueue\JobType\ArchiveCitizenProposalJob;
use Drupal\hoeringsportal_citizen_proposal_archiving\Renderer\Renderer;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Helper for citizen proposal archiving.
 */
final class Helper implements EventSubscriberInterface, LoggerAwareInterface, LoggerInterface {
  use LoggerAwareTrait;
  use LoggerTrait;

  /**
   * The queue storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorage|\Drupal\Core\Entity\EntityStorageInterface
   */
  protected ConfigEntityStorageInterface $queueStorage;

  /**
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    readonly private CitizenProposalHelper $citizenProposalHelper,
    readonly private AbstractArchiver $archiver,
    readonly private Renderer $renderer,
    LoggerInterface $logger
  ) {
    $this->queueStorage = $entityTypeManager->getStorage('advancedqueue_queue');
    $this->setLogger($logger);
  }

  /**
   * Load queue.
   */
  private function loadQueue(): QueueInterface {
    $queueName = 'hoeringsportal_citizen_proposal_archiving';
    $queue = $this->queueStorage->load($queueName);
    if (NULL === $queue) {
      throw new RuntimeException(sprintf('Cannot load queue %s', $queueName));
    }

    return $queue;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      EntityEventType::INSERT => 'update',
      EntityEventType::UPDATE => 'update',
    ];
  }

  /**
   * Event handler (cf. self::getSubscribedEvents()).
   *
   * @see self::getSubscribedEvents()
   */
  public function update(EntityEvent $event) {
    $entity = $event->getEntity();
    if ($entity instanceof NodeInterface
      && $this->citizenProposalHelper->isCitizenProposal($entity)) {
      $this->createJob($entity);
    }
  }

  /**
   * Create a job.
   *
   * @see self::processJob()
   */
  public function createJob(NodeInterface $node): ?Job {
    try {
      $job = Job::create(ArchiveCitizenProposalJob::class, [
        'node_id' => $node->id(),
        'node_changed_at' => $node->getChangedTime(),
      ]);
      $queue = $this->loadQueue();
      $queue->enqueueJob($job);

      $this->notice('Job for archiving citizen proposal @label added to the queue @queue.', [
        '@label' => $node->label(),
        '@queue' => $queue->id(),
      ]);

      return $job;
    }
    catch (\Exception $exception) {
      $this->error('Error adding job for archiving citizen proposal @label: @message.', [
        '@label' => $node->label(),
        '@message' => $exception->getMessage(),
        'exception' => $exception,
      ]);

      return NULL;
    }
  }

  /**
   * Process a job.
   *
   * @see self::createJob()
   */
  public function processJob(Job $job): JobResult {
    try {
      [
        'node_id' => $nodeId,
        'node_changed_at' => $nodeChangedAt,
      ] = $job->getPayload();
      $node = $this->citizenProposalHelper->loadCitizenProposal($nodeId);

      if ($node->getChangedTime() > $nodeChangedAt) {
        $this->debug('Node @label changed after requested archival time (@some_time > @another_time). Skipping.', [
          '@label' => $node->label(),
          '@some_time' => DrupalDateTime::createFromTimestamp($node->getChangedTime())->format(DrupalDateTime::FORMAT),
          '@another_time' => DrupalDateTime::createFromTimestamp($nodeChangedAt)->format(DrupalDateTime::FORMAT),
        ]);

        return JobResult::success('Skipped');
      }
      else {
        $content = $this->renderPdf($node);
        $this->archiver->archive($node, $content, 'pdf');

        $this->notice('Citizen proposal @label archived', [
          '@label' => $node->label(),
        ]);

        return JobResult::success('Citizen proposal archived');
      }
    }
    catch (\Throwable $throwable) {
      $this->error('Error processing job: @message', [
        '@message' => $throwable->getMessage(),
        'throwable' => $throwable,
      ]);

      return JobResult::failure($throwable->getMessage());
    }
  }

  /**
   * Render citizen proposal as PDF.
   */
  public function renderPdf(NodeInterface $node): string {
    $supportCount = $this->citizenProposalHelper->getProposalSupportCount($node->id());

    return $this->renderer->renderPdf($node, [
      'support' => [
        'count' => $supportCount,
        'percentage' => $this->citizenProposalHelper->calculateSupportPercentage($supportCount),
      ],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    $this->logger->log($level, $message, $context);
  }

}
