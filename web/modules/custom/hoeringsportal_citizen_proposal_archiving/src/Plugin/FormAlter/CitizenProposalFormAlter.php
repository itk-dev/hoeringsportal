<?php

namespace Drupal\hoeringsportal_citizen_proposal_archiving\Plugin\FormAlter;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\hoeringsportal_citizen_proposal_archiving\Archiver\GetOrganizedArchiver;
use Drupal\node\NodeInterface;
use Drupal\pluginformalter\Plugin\FormAlterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Altering citizen proposal edit form.
 *
 * @FormAlter(
 *   id = "hoeringsportal_citizen_proposal_archiving",
 *   label = @Translation("Altering citizen proposal edit form."),
 *   form_id = {
 *    "node_citizen_proposal_edit_form",
 *   }
 * )
 */
class CitizenProposalFormAlter extends FormAlterBase {

  /**
   * Constructor.
   */
  public function __construct(
    readonly private GetOrganizedArchiver $archiver,
    readonly private Renderer $renderer,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get(GetOrganizedArchiver::class),
      $container->get('renderer'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formAlter(array &$form, FormStateInterface &$form_state, $form_id) {
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

      $form['field_getorganized_case_id']['#suffix'] = $this->renderer->renderPlain($build);
    }
  }

}
