<?php

namespace Drupal\hoeringsportal_citizen_proposal\Helper;

use Drupal\Core\Database\Connection;
use Drupal\Core\File\FileUrlGenerator;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\State\State;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\hoeringsportal_citizen_proposal\Exception\RuntimeException;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Symfony\Component\Serializer\Serializer;
use Drupal\Core\Site\Settings;

/**
 * A helper class for the module.
 */
class Helper implements LoggerAwareInterface {
  use MessengerTrait;
  use StringTranslationTrait;
  use LoggerAwareTrait;
  use LoggerTrait;

  private const CITIZEN_PROPOSAL_ENTITY = 'citizen_proposal_entity';
  private const PROPOSAL_PERIOD_LENGTH = '+180 days';
  private const PROPOSAL_SUPPORT_REQUIRED = 30000;

  /**
   * Constructor for the citizen proposal helper class.
   */
  public function __construct(
    readonly private PrivateTempStoreFactory $tempStoreFactory,
    readonly private Serializer $serializer,
    readonly private State $state,
    readonly private FileUrlGenerator $fileUrlGenerator,
    readonly private RouteMatchInterface $routeMatch,
    readonly private Connection $connection,
    LoggerChannel $logger
  ) {
    $this->setLogger($logger);
  }

  /**
   * Validate that the tempstore contains a node and return it.
   *
   * @return object|null
   *   The entity or NULL if no valid entity was found.
   */
  public function getDraftProposal(): ?object {
    $entitySerialized = $this->getProposalStorage()->get(self::CITIZEN_PROPOSAL_ENTITY);
    $node = is_string($entitySerialized) ? $this->serializer->deserialize($entitySerialized, Node::class, 'json') : NULL;

    return isset($node) && $node instanceof Node ? $node : NULL;
  }

  /**
   * Check if we have a draft proposal.
   */
  public function hasDraftProposal(): bool {
    return !empty($this->getDraftProposal());
  }

  /**
   * Delete the citizen_proposal_entity tempstore.
   */
  public function deleteDraftProposal(): void {
    try {
      $this->getProposalStorage()->delete(self::CITIZEN_PROPOSAL_ENTITY);
    }
    catch (\Exception $e) {
    }
  }

  /**
   * Add entity to temp store.
   */
  public function setDraftProposal($entity): void {
    $nodeSerialized = $this->serializer->serialize($entity, 'json');
    try {
      $this->getProposalStorage()->set(
        self::CITIZEN_PROPOSAL_ENTITY, $nodeSerialized
      );
    }
    catch (\Exception $exception) {
      $this->logger->error('Error setting draft proposal: @message', [
        '@message' => $exception->getMessage(),
        'entity' => $entity,
      ]);
    }
  }

  /**
   * Preprocess forms related to citizen proposal.
   */
  public function preprocessForm(&$variables): void {
    $variables['admin_form_state_values'] = $this->state->get('citizen_proposal_admin_form_values');
  }

  /**
   * Preprocess citizen proposal nodes.
   */
  public function preprocessNode(&$variables): void {
    if ('citizen_proposal' !== $variables['node']->bundle()) {
      return;
    }

    $proposalSupportCount = $this->getProposalSupportCount((int) $variables['node']->nid->value);

    $variables['proposal_support_count'] = $proposalSupportCount;
    $variables['proposal_support_percentage'] = $proposalSupportCount ? $this->calculateSupportPercentage((int) $proposalSupportCount) . '%' : '0%';
  }

  /**
   * Save proposal support to db.
   *
   * @param string $userUuid
   *   The user UUID.
   * @param \Drupal\node\NodeInterface $node
   *   The proposal node.
   * @param array $values
   *   The values to save.
   */
  public function saveSupport(string $userUuid, NodeInterface $node, array $values): void {
    try {
      if (NULL !== $this->getUserSupportedAt($userUuid, $node)) {
        throw new RuntimeException('User @user already supports proposal @proposal', [
          '@user' => $userUuid,
          '@proposal' => $node->id(),
        ]);
      }

      $values['user_uuid'] = $userUuid;
      $values['node_id'] = $node->id();
      $this->connection->insert('hoeringsportal_citizen_proposal_support')
        ->fields($values)
        ->execute();
      $this->messenger()->addStatus($this->t('Thank you! Your support has been registered.'));
    }
    catch (\Exception $exception) {
      $this->logger->error('Error saving support: @message', [
        '@message' => $exception->getMessage(),
        'exception' => $exception,
        'values' => $values,
      ]);
      $this->messenger()->addWarning($this->t('Something went wrong. Your support was not registered.'));
    }
  }

  /**
   * Get time when user supported a proposal if any.
   */
  public function getUserSupportedAt(string $userUuid, NodeInterface $node): ?\DateTimeInterface {
    $result = $this->connection->select('hoeringsportal_citizen_proposal_support', 's')
      ->fields('s')
      ->condition('user_uuid', $userUuid)
      ->condition('node_id', $node->id())
      ->execute()
      ->fetchObject();

    if ($result) {
      return new \DateTimeImmutable('@' . $result->created);
    }

    return NULL;
  }

  /**
   * Page attachments related to citizen proposal.
   */
  public function proposalPageAttachments(&$page): void {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->routeMatch->getParameter('node');
    if (!$node) {
      return;
    }

    if ('citizen_proposal' !== $node->bundle()) {
      return;
    }

    $og = [
      'title' => [
        '#tag' => 'meta',
        '#attributes' => [
          'property' => 'og:title',
          'content' => $node->getTitle(),
        ],
      ],
      'image' => [
        '#tag' => 'meta',
        '#attributes' => [
          'property' => 'og:image',
          'content' => $this->fileUrlGenerator->generateAbsoluteString(\Drupal::config('hoeringsportal.settings')->get('logo.path')),
        ],
      ],
      'description' => [
        '#tag' => 'meta',
        '#attributes' => [
          'property' => 'og:description',
          'content' => substr($node->field_proposal->value, 0, 150) . '...',
        ],
      ],
      'url' => [
        '#tag' => 'meta',
        '#attributes' => [
          'property' => 'og:url',
          'content' => Url::fromRoute('<current>', [], ['absolute' => 'true'])->toString(),
        ],
      ],
    ];

    foreach ($og as $key => $attr) {
      $page['#attached']['html_head'][] = [$attr, $key];
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_presave().
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A proposal entity.
   */
  public function nodeEntityPresave(EntityInterface $entity): void {
    if ('citizen_proposal' !== $entity->bundle() || !$entity instanceof Node) {
      return;
    }
    $proposalOriginal = $entity->original;
    // Allow changing this value in settings.php.
    $periodLength = Settings::get('proposal_period_length', self::PROPOSAL_PERIOD_LENGTH);

    $start = new DrupalDateTime();
    $end = new DrupalDateTime($periodLength);
    $storageTimezone = new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE);

    // If content is being published in this node->save() action.
    if ($entity->isPublished() && !$proposalOriginal->isPublished()) {
      // Set proposal period.
      $entity->set('field_vote_start', $start->setTimezone($storageTimezone)->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT));
      $entity->set('field_vote_end', $end->setTimezone($storageTimezone)->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT));
      $entity->set('field_content_state', 'active');
    }
  }

  /**
   * Get citizen proposal support count.
   *
   * @param int $nid
   *   The id of the citizen proposal to get support count for.
   *
   * @return mixed
   *   A single field from the next record, or FALSE if there is no next record.
   */
  public function getProposalSupportCount(int $nid): mixed {
    return $this->connection->select('hoeringsportal_citizen_proposal_support')
      ->condition('node_id', $nid)
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  /**
   * Calculate the support percentage of a proposal, from the support count.
   *
   * @param int $proposalSupportCount
   *   The support count af a proposal.
   *
   * @return int
   *   A percentage value as an integer.
   */
  public function calculateSupportPercentage(int $proposalSupportCount): int {
    // Allow changing this value in settings.php.
    $proposalSupportRequired = Settings::get('proposal_support_required', self::PROPOSAL_SUPPORT_REQUIRED);

    return min(
        100,
        (int) ceil($proposalSupportCount / $proposalSupportRequired * 100)
      );
  }

  /**
   * The proposal temp store.
   *
   * @return \Drupal\Core\TempStore\PrivateTempStore
   *   The proposal tempstore.
   */
  private function getProposalStorage(): PrivateTempStore {
    return $this->tempStoreFactory->get('hoeringsportal_citizen_proposal');
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    $this->logger->log($level, $message, $context);
  }

}
