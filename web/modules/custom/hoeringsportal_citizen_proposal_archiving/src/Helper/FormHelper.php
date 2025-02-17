<?php

namespace Drupal\hoeringsportal_citizen_proposal_archiving\Helper;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\hoeringsportal_citizen_proposal_archiving\Archiver\GetOrganizedArchiver;
use Drupal\node\NodeInterface;

/**
 * Form helper.
 */
final class FormHelper {
  use StringTranslationTrait;

  /**
   * Constructor.
   */
  public function __construct(
    readonly private GetOrganizedArchiver $archiver,
    readonly private RendererInterface $renderer,
  ) {
  }

  /**
   * Implements hook_form_alter().
   */
  public function alterForm(array &$form, FormStateInterface $form_state, string $form_id): void {
    if ('node_citizen_proposal_edit_form' !== $form_id) {
      return;
    }

    $formObject = $form_state->getFormObject();
    assert($formObject instanceof EntityFormInterface);
    $node = $formObject->getEntity();
    assert($node instanceof NodeInterface);

    $children = [];

    $info = $this->archiver->getArchivalInfo($node);
    if (isset($info['archived_at'])) {
      $children += [
        'archived_at' => [
          'header' => [
            '#type' => 'html_tag',
            '#tag' => 'dt',
            '#value' => $this->t('Archived at'),
          ],
          'content' => [
            '#type' => 'html_tag',
            '#tag' => 'dd',
            '#value' => DrupalDateTime::createFromTimestamp($info['archived_at'])->format(DrupalDateTime::FORMAT),
          ],
        ],
      ];
    }

    if (isset($info['archive_url'])) {
      $children += [
        'archive_url' => [
          'header' => [
            '#type' => 'html_tag',
            '#tag' => 'dt',
            '#value' => $this->t('Archive URL'),
          ],
          'content' => [
            '#type' => 'html_tag',
            '#tag' => 'dd',
            'child' => [
              '#type' => 'link',
              '#title' => $info['archive_url'],
              '#url' => Url::fromUri($info['archive_url']),
            ],
          ],
        ],
      ];
    }

    if (!empty($children)) {
      $build = [
        '#type' => 'html_tag',
        '#tag' => 'dl',
        'child' => $children,
      ];

      $form['field_getorganized_case_id']['#suffix'] = $this->renderer->renderInIsolation($build);
    }
  }

}
