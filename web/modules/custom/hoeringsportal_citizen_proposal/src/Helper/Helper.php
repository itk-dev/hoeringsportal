<?php

namespace Drupal\hoeringsportal_citizen_proposal\Helper;

use Drupal\Core\State\State;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Serializer\Serializer;
use Drupal\Core\Messenger\MessengerTrait;

/**
 * A helper class for the module.
 */
class Helper {
  use MessengerTrait;

  /**
   * Constructor for the citizen proposal helper class.
   */
  public function __construct(
    readonly private PrivateTempStoreFactory $tempStoreFactory,
    readonly private Serializer $serializer,
    readonly private State $state,
  ) {
  }

  /**
   * Validate that the tempstore contains a node and return it.
   *
   * @return object|null
   *   The entity or NULL if no valid entity was found.
   */
  public function tempStoreValid(): ?object {
    $entitySerialized = $this->tempStoreFactory->get('hoeringsportal_citizen_proposal')->get('citizen_proposal_entity');
    $node = is_string($entitySerialized) ? $this->serializer->deserialize($entitySerialized, Node::class, 'json') : NULL;

    return isset($node) && $node instanceof (Node::class) ? $node : NULL;
  }

  /**
   * Delete the citizen_proposal_entity tempstore.
   */
  public function tempStoreDelete(): void {
    try {
      $this->tempStoreFactory->get('hoeringsportal_citizen_proposal')->delete('citizen_proposal_entity');
    }
    catch (\Exception $e) {
    }
  }

  /**
   * Add entity to temp store.
   */
  public function tempStoreAddEntity($entity): void {
    $nodeSerialized = $this->serializer->serialize($entity, 'json');
    try {
      $this->tempStoreFactory->get('hoeringsportal_citizen_proposal')->set('citizen_proposal_entity', $nodeSerialized);
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
    $this->messenger()->addWarning('Could not find a proposal to approve.');
    $url = Url::fromRoute('hoeringsportal_citizen_proposal.citizen_proposal.proposal_add');

    return new RedirectResponse($url->toString());
  }

  /**
   * Preprocess forms related to citizen proposal.
   */
  public function preprocessForm(&$variables): void {
    $variables['admin_form_state_values'] = $this->state->get('citizen_proposal_admin_form_values');
  }

}
