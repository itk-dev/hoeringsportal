<?php

namespace Drupal\hoeringsportal_citizen_proposal\Helper;

use Drupal\Core\State\State;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Serializer\Serializer;
use Drupal\Core\File\FileUrlGenerator;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * A helper class for the module.
 */
class Helper {
  use MessengerTrait;
  use StringTranslationTrait;

  private const CITIZEN_PROPOSAL_ENTITY = 'citizen_proposal_entity';

  /**
   * Constructor for the citizen proposal helper class.
   */
  public function __construct(
    readonly private PrivateTempStoreFactory $tempStoreFactory,
    readonly private Serializer $serializer,
    readonly private State $state,
    readonly private FileUrlGenerator $fileUrlGenerator,
    readonly private RouteMatchInterface $routeMatch,
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
      $this->getProposalStorage()->set(self::CITIZEN_PROPOSAL_ENTITY, $nodeSerialized);
    }
    catch (\Exception $e) {
    }
  }

  /**
   * Abandon submission and add redirect response.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response.
   */
  public function abandonSubmission(): RedirectResponse {
    $this->messenger()->addWarning($this->t('Could not find a proposal to approve.'));
    $url = Url::fromRoute('hoeringsportal_citizen_proposal.citizen_proposal.proposal_add');

    return new RedirectResponse($url->toString());
  }

  /**
   * Preprocess forms related to citizen proposal.
   */
  public function preprocessForm(&$variables): void {
    $variables['admin_form_state_values'] = $this->state->get('citizen_proposal_admin_form_values');
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
   * The proposal temp store.
   *
   * @return \Drupal\Core\TempStore\PrivateTempStore
   *   The proposal tempstore.
   */
  private function getProposalStorage(): PrivateTempStore {
    return $this->tempStoreFactory->get('hoeringsportal_citizen_proposal');
  }

}
