<?php

namespace Drupal\hoeringsportal_citizen_proposal\Helper;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGenerator;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\hoeringsportal_citizen_proposal\Exception\RuntimeException;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Symfony\Component\Serializer\Serializer;

/**
 * A helper class for the module.
 */
class Helper implements LoggerAwareInterface {
  use MessengerTrait;
  use StringTranslationTrait;
  use LoggerAwareTrait;
  use LoggerTrait;

  private const ADMIN_FORM_VALUES_STATE_KEY = 'citizen_proposal_admin_form_values';

  private const CITIZEN_PROPOSAL_ENTITY = 'citizen_proposal_entity';
  private const PROPOSAL_PERIOD_LENGTH = '+180 days';
  private const PROPOSAL_SUPPORT_REQUIRED = 30000;

  /**
   * Constructor for the citizen proposal helper class.
   */
  public function __construct(
    readonly private PrivateTempStoreFactory $tempStoreFactory,
    readonly private Serializer $serializer,
    readonly private StateInterface $state,
    readonly private FileUrlGenerator $fileUrlGenerator,
    readonly private RouteMatchInterface $routeMatch,
    readonly private Connection $connection,
    readonly private EntityTypeManagerInterface $entityTypeManager,
    readonly private TimeInterface $time,
    LoggerChannel $logger,
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
    $variables['admin_form_state_values'] = $this->getAdminValues();
  }

  /**
   * Get all admin values.
   *
   * @param mixed|null $default
   *   The default value.
   */
  public function getAdminValues(mixed $default = NULL): mixed {
    return $this->getAdminValue(NULL, NULL);
  }

  /**
   * Get admin value.
   *
   * @param string|array|null $key
   *   The key to get. If key is empty all values are returned.
   * @param mixed|null $default
   *   The default value.
   *
   * @return string|array|null
   *   The value if any. Otherwise the default value.
   */
  public function getAdminValue(string|array|null $key = NULL, mixed $default = NULL) {
    $values = $this->state->get(self::ADMIN_FORM_VALUES_STATE_KEY) ?: [];
    $value = empty($key)
      ? $values
      : NestedArray::getValue($values, (array) $key);

    if (is_string($value)) {
      $value = trim($value);
    }

    return $value ?: $default;
  }

  /**
   * Set all admin values.
   *
   * @param array $values
   *   The values.
   */
  public function setAdminValues(array $values): void {
    $this->setAdminValue(NULL, $values);
  }

  /**
   * Set an admin value.
   *
   * @param string|array|null $key
   *   The key. If the key is empty all admin values are set to the value.
   * @param mixed $value
   *   The value.
   */
  public function setAdminValue(string|array|null $key, mixed $value): void {
    if (empty($key)) {
      if (!is_array($value)) {
        throw new \TypeError('Value must be an array');
      }
      $values = $value;
    }
    else {
      $values = $this->getAdminValues();
      NestedArray::setValue($values, (array) $key, $value);
    }

    $this->state->set(self::ADMIN_FORM_VALUES_STATE_KEY, $values);
  }

  /**
   * Preprocess citizen proposal nodes.
   */
  public function preprocessNode(&$variables): void {
    /** @var \Drupal\node\Entity\NodeInterface $node */
    $node = $variables['node'];

    if ('citizen_proposal' !== $node->bundle()) {
      return;
    }

    $proposalSupportCount = $this->getProposalSupportCount((int) $node->id());

    $variables['proposal_support_count'] = $proposalSupportCount;
    $variables['proposal_support_required'] = $this->getProposalSupportRequired();
    $variables['proposal_support_percentage'] = $this->calculateSupportPercentage($proposalSupportCount);
  }

  /**
   * Save proposal support to db.
   *
   * @param string $userIdentifier
   *   The user identifier.
   * @param \Drupal\node\NodeInterface $node
   *   The proposal node.
   * @param array $values
   *   The values to save.
   */
  public function saveSupport(string $userIdentifier, NodeInterface $node, array $values): void {
    if (NULL !== $this->getUserSupportedAt($userIdentifier, $node)) {
      throw new RuntimeException(sprintf('User %s already supports proposal %s', $userIdentifier, $node->id()));
    }

    try {
      $values['user_identifier'] = $userIdentifier;
      $values['node_id'] = $node->id();
      $values['allow_email'] = ($values['allow_email'] ?? FALSE) ? 1 : 0;
      // Set some defaults.
      $values += [
        'created' => $this->time->getRequestTime(),
      ];
      $this->connection->insert('hoeringsportal_citizen_proposal_support')
        ->fields($values)
        ->execute();

      // Mark node as changed now, and save to flush cache and notify any node
      // change listeners.
      $node
        ->setChangedTime($this->time->getRequestTime())
        ->save();
    }
    catch (\Exception $exception) {
      $this->logger->error('Error saving support: @message', [
        '@message' => $exception->getMessage(),
        'exception' => $exception,
        'values' => $values,
      ]);

      throw $exception;
    }
  }

  /**
   * Get time when user supported a proposal if any.
   */
  public function getUserSupportedAt(string $userIdentifier, NodeInterface $node): ?\DateTimeInterface {
    $result = $this->connection->select('hoeringsportal_citizen_proposal_support', 's')
      ->fields('s')
      ->condition('user_identifier', $userIdentifier)
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
    if ($entity->isPublished() && !$proposalOriginal?->isPublished()) {
      // Set proposal period.
      $entity->set('field_vote_start', $start->setTimezone($storageTimezone)->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT));
      $entity->set('field_vote_end', $end->setTimezone($storageTimezone)->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT));
      $entity->set('field_content_state', 'active');
    }
  }

  /**
   * Find overdue proposals.
   *
   * @return array
   *   A list of overdue proposal ids.
   */
  public function findOverdueProposals(): array {
    $now = new DrupalDateTime();
    try {
      return $this->entityTypeManager
        ->getStorage('node')
        ->getQuery()
        ->condition('status', 1, '=')
        ->condition('type', 'citizen_proposal')
        ->condition('field_content_state', 'active', '=')
        ->condition('field_vote_end', $now->format(DateTimeItemInterface::DATE_STORAGE_FORMAT), '<')
        ->accessCheck(FALSE)
        ->execute();
    }
    catch (\Exception $exception) {
      $this->logger->error('Error finding overdue proposals: @message', [
        '@message' => $exception->getMessage(),
      ]);

      return [];
    }

  }

  /**
   * Finish a proposal by changing it's state and som other fields.
   *
   * @param int $nid
   *   The id of the proposal to finish.
   */
  public function finishProposal(int $nid): void {
    try {
      $entity = $this->entityTypeManager
        ->getStorage('node')
        ->load($nid);

      if (!$entity instanceof Node) {
        return;
      }

      // Set proposal state.
      $entity->set('field_content_state', 'finished');

      // Anonymize proposal if it didn't reach enough votes.
      if ($this->hasEnoughVotes($nid)) {
        $entity->set('field_author_name', '');
        $entity->set('field_author_email', '');
      }

      $entity->save();
    }
    catch (\Exception $exception) {
      $this->logger->error('Error finishing proposal with id:@nid: @message', [
        '@nid' => $nid,
        '@message' => $exception->getMessage(),
      ]);
    }
  }

  /**
   * Get citizen proposal support count.
   *
   * @param int $nid
   *   The id of the citizen proposal to get support count for.
   *
   * @return int
   *   A single field from the next record, or 0 if there is no next record.
   */
  public function getProposalSupportCount(int $nid): int {
    return $this->connection->select('hoeringsportal_citizen_proposal_support')
      ->condition('node_id', $nid)
      ->countQuery()
      ->execute()
      ->fetchField() ?? 0;
  }

  /**
   * Calculate the support percentage of a proposal, from the support count.
   *
   * @param int $proposalSupportCount
   *   The support count af a proposal.
   *
   * @return float
   *   A percentage value.
   */
  public function calculateSupportPercentage(int $proposalSupportCount): float {
    if (!$proposalSupportCount) {
      return 0;
    }

    return min(
      100,
      $proposalSupportCount / $this->getProposalSupportRequired() * 100
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
   * Determine if a proposal has enough votes.
   *
   * @param int $nid
   *   The support a proposal has received.
   *
   * @return bool
   *   Whether the proposal has enough votes.
   */
  private function hasEnoughVotes(int $nid): bool {
    return $this->getProposalSupportCount($nid) > $this->getProposalSupportRequired();
  }

  /**
   * Get the required support count.
   *
   * @return int
   *   The support proposals require.
   */
  public function getProposalSupportRequired(): int {
    // Allow changing this value in settings.php.
    return (int) Settings::get('proposal_support_required', self::PROPOSAL_SUPPORT_REQUIRED);
  }

  /**
   * Load citizen proposal.
   */
  public function loadCitizenProposal(int $id): NodeInterface {
    $node = $this->entityTypeManager->getStorage('node')->load($id);
    if (NULL === $node) {
      throw new RuntimeException(sprintf('Cannot load node %s', $id));
    }
    if (!$this->isCitizenProposal($node)) {
      throw new RuntimeException(sprintf('Node %s (%s) is not a citizen proposal', $node->id(), $node->label()));
    }

    return $node;
  }

  /**
   * Check is a node is a citizen proposal.
   */
  public function isCitizenProposal(NodeInterface $node): bool {
    return 'citizen_proposal' === $node->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []): void {
    $this->logger->log($level, $message, $context);
  }

  /**
   * Decide if a citizen proposal is active, i.e. can be supported.
   */
  public function isActive(NodeInterface $node): bool {
    return $this->isCitizenProposal($node)
      && 'active' === $node->get('field_content_state')->getString();
  }

  /**
   * Get vote end date.
   */
  public function getVoteEndDate(NodeInterface $node): int {
    if (!$this->isCitizenProposal($node)) {
      return NULL;
    }

    return $node->field_vote_end->date->getTimestamp();
  }

  /**
   * Implements hook_views_data().
   */
  public function viewsData(): array {
    return [
      // Make our custom table available for views.
      'hoeringsportal_citizen_proposal_support' => [
        'table' => [
          'group' => $this->t('Citizen proposal'),
          'provider' => 'hoeringsportal_citizen_proposal',
          'base' => [
            'field' => 'id',
            'title' => $this->t('Citizen proposal support'),
            'help' => $this->t('Citizen proposal support related to citizen proposal nodes'),
          ],
        ],

        'id' => [
          'title' => $this->t('Id'),
          'help' => $this->t('Citizen proposal support'),
          'field' => [
            'id' => 'numeric',
          ],
          'sort' => [
            'id' => 'standard',
          ],
          'filter' => [
            'id' => 'numeric',
          ],
          'argument' => [
            'id' => 'numeric',
          ],
        ],

        'node_id' => [
          'title' => $this->t('Citizen proposal'),
          'help' => $this->t('Citizen proposal'),
          'field' => [
            'id' => 'numeric',
          ],
          'sort' => [
            'id' => 'standard',
          ],
          'filter' => [
            'id' => 'numeric',
          ],
          'argument' => [
            'id' => 'numeric',
          ],
          'relationship' => [
            'base' => 'node_field_data',
            'id' => 'standard',
            'base field' => 'nid',
            'label' => $this->t('Citizen proposal'),
          ],
        ],

        'user_identifier' => [
          'title' => $this->t('User identifier'),
          'help' => $this->t('User identifier'),
          'field' => [
            'id' => 'standard',
          ],
          'sort' => [
            'id' => 'standard',
          ],
          'filter' => [
            'id' => 'string',
          ],
          'argument' => [
            'id' => 'string',
          ],
        ],

        'user_name' => [
          'title' => $this->t('User name'),
          'help' => $this->t('User name'),
          'field' => [
            'id' => 'standard',
          ],
          'sort' => [
            'id' => 'standard',
          ],
          'filter' => [
            'id' => 'string',
          ],
          'argument' => [
            'id' => 'string',
          ],
        ],

        'user_email' => [
          'title' => $this->t('User email'),
          'help' => $this->t('User email'),
          'field' => [
            'id' => 'standard',
          ],
          'sort' => [
            'id' => 'standard',
          ],
          'filter' => [
            'id' => 'string',
          ],
          'argument' => [
            'id' => 'string',
          ],
        ],

        'allow_email' => [
          'title' => $this->t('Allow email'),
          'help' => $this->t('Allow email'),
          'field' => [
            'id' => 'numeric',
          ],
          'sort' => [
            'id' => 'standard',
          ],
          'filter' => [
            'id' => 'numeric',
          ],
          'argument' => [
            'id' => 'numeric',
          ],
        ],

        'created' => [
          'title' => $this->t('Created'),
          'help' => $this->t('Created'),
          'field' => [
            'id' => 'numeric',
          ],
          'sort' => [
            'id' => 'standard',
          ],
          'filter' => [
            'id' => 'numeric',
          ],
          'argument' => [
            'id' => 'numeric',
          ],
        ],
      ],

      // @todo Set up a reverse relation.
      'node' => [
        'hoeringsportal_citizen_proposal_support' => [
          'title' => $this->t('Citizen proposal support (title)'),
          'help' => $this->t('Citizen proposal support (help)'),
          'relationship' => [
            'group' => $this->t('Citizen proposal support (group)'),
            'id' => 'entity_reverse',
            'field_name' => 'hoeringsportal_citizen_proposal_support',
            'field table' => 'hoeringsportal_citizen_proposal_support',
            'field field' => 'node_id',
            'base' => 'node_field_data',
            'base field' => 'nid',
            'label' => $this->t('Citizen proposal support (relationship)'),
          ],
        ],
      ],
    ];
  }

}
