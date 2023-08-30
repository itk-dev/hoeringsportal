<?php

namespace Drupal\hoeringsportal_citizen_proposal\Helper;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformEntityStorageInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Webform helper.
 */
class WebformHelper {

  /**
   * Constructor.
   */
  public function __construct(
    readonly private WebformEntityStorageInterface $webformStorage,
    readonly private ImmutableConfig $webformConfig,
    readonly private PrivateTempStore $tempStore
  ) {
  }

  /**
   * Load survey webforms indexed by id.
   *
   * @return array|WebformInterface[]
   *   The webforms.
   */
  public function loadSurveyWebforms(): array {
    return $this->webformStorage->loadMultiple();
  }

  /**
   * Load webform.
   *
   * @param string $id
   *   The webform id.
   *
   * @return \Drupal\webform\WebformInterface|null
   *   The webform if found.
   */
  public function loadWebform(string $id): ?WebformInterface {
    return $this->webformStorage->load($id) ?: NULL;
  }

  /**
   * Render webform elements into a form.
   *
   * @param \Drupal\webform\WebformInterface $webform
   *   The webform.
   * @param array $form
   *   The target for.
   */
  public function renderWebformElements(WebformInterface $webform, array &$form): void {
    $response = $this->getSurveyResponse($webform);
    foreach ($webform->getElementsDecoded() as $key => $element) {
      if ($this->isRenderableElement($element)) {
        $form[$key] = $element;
        if (isset($response[$key])) {
          $form[$key]['#default_value'] = $response[$key];
        }
      }
    }
  }

  /**
   * Set survey response.
   */
  public function setSurveyResponse(WebformInterface $webform, array $response) {
    $this->tempStore->set($this->createTempStoreKey($webform), $response);
  }

  /**
   * Get survey response.
   */
  public function getSurveyResponse(WebformInterface $webform): ?array {
    return $this->tempStore->get($this->createTempStoreKey($webform));
  }

  /**
   * Delete survey response.
   */
  private function deleteSurveyResponse(WebformInterface $webform): bool {
    return $this->tempStore->delete($this->createTempStoreKey($webform));
  }

  /**
   * Save survey response by creating a webform submission.
   */
  public function saveSurveyResponse(WebformInterface $webform, ContentEntityInterface $entity): WebformSubmissionInterface {
    $response = $this->getSurveyResponse($webform);

    // Add entity reference to response.
    foreach ($webform->getElementsDecodedAndFlattened() as $key => $element) {
      if ('entity_autocomplete' === ($element['#type'] ?? NULL)
        && $entity->getEntityTypeId() === ($element['#target_type'] ?? NULL)
        && isset($element['#selection_settings']['target_bundles'][$entity->bundle()])) {
        $response[$key] = $entity->id();
      }
    }

    $submission = WebformSubmission::create([
      'data' => $response,
      'webform_id' => $webform->id(),
    ]);
    $submission->save();

    $this->deleteSurveyResponse($webform);

    return $submission;
  }

  /**
   * Create temp store key.
   *
   * This key will have a user id prepended before being stored in the database
   * table key_value_expire. The key name in this table allows at most 128
   * character and for anonymous users a random session id with a length around
   * 43 will be prepended (cf. Drupal\Core\TempStore\PrivateTempStore::set()).
   * A webform id can be at most 32 characters which leaves us with at most
   * 128-43-32 = 53 characters for our key prefix.
   */
  private function createTempStoreKey(WebformInterface $webform): string {
    return 'citizen_proposal_webform:' . $webform->id();
  }

  /**
   * Check if an element is renderable.
   *
   * @param array $element
   *   The elements.
   *
   * @return bool
   *   Whether the element is renderable.
   */
  private function isRenderableElement(array $element): bool {
    $disallowedElements = [
      'entity_autocomplete',
      'webform_actions',
    ];

    return !in_array($element['#type'] ?? NULL, $disallowedElements, TRUE)
      && !$this->isExcludedWebformElement($element);
  }

  /**
   * Check if an element is excluded from webforms.
   *
   * @param array $element
   *   The element.
   *
   * @return bool
   *   Whether the element is excluded.
   */
  private function isExcludedWebformElement(array $element): bool {
    $excludedElements = (array) ($this->webformConfig->get('element.excluded_elements') ?? NULL);

    return isset($excludedElements[$element['#type'] ?? NULL]);
  }

}
