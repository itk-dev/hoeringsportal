<?php

namespace Drupal\hoeringsportal_citizen_proposal\Helper;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\State\State;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\Serializer\Serializer;
use Drupal\Core\File\FileUrlGenerator;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Site\Settings;

/**
 * A helper class for the module.
 */
class Helper {
  use MessengerTrait;
  use StringTranslationTrait;

  private const CITIZEN_PROPOSAL_ENTITY = 'citizen_proposal_entity';
  private const PROPOSAL_PERIOD_LENGTH = '+180 days';

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
  ) {
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
    catch (\Exception $e) {
    }
  }

  /**
   * Preprocess forms related to citizen proposal.
   */
  public function preprocessForm(&$variables): void {
    $variables['admin_form_state_values'] = $this->state->get('citizen_proposal_admin_form_values');
  }

  /**
   * Save proposal support to db.
   *
   * @param array $values
   *   The values to save.
   */
  public function saveSupport(array $values): void {
    try {
      $this->connection->insert('hoeringsportal_citizen_proposal_support')
        ->fields($values)
        ->execute();
      $this->messenger()->addStatus($this->t('Thank you! Your support has been registered.'));
    }
    catch (\Exception) {
      $this->messenger()->addWarning($this->t('Something went wrong. Your support was not registered.'));
    }

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
   * The proposal temp store.
   *
   * @return \Drupal\Core\TempStore\PrivateTempStore
   *   The proposal tempstore.
   */
  private function getProposalStorage(): PrivateTempStore {
    return $this->tempStoreFactory->get('hoeringsportal_citizen_proposal');
  }

}
