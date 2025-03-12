<?php

namespace Drupal\hoeringsportal_hearing\Helper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Form helper.
 */
class FormHelper {
  use StringTranslationTrait;

  /**
   * Implements hook_form_alter().
   */
  public function formAlter(array &$form, FormStateInterface $form_state, string $form_id) {
    if ('node_hearing_edit_form' === $form_id && isset($form['field_reply_deadline'])) {
      $node = $form_state->getFormObject()->getEntity();
      if (NULL !== $node->id()) {
        $form['field_reply_deadline']['#suffix'] = Link::fromTextAndUrl(
          t('Add hearing reply'),
          Url::fromRoute(
            'hoeringsportal_deskpro.hearing.ticket_add',
            ['node' => $node->id()],
            ['attributes' => ['class' => ['button']]]
          )
        )->toString();
      }
    }

    if ('node_hearing_edit_form' === $form_id || 'node_hearing_form' === $form_id) {
      $form['#attached']['library'][] = 'hoeringsportal_hearing/hearing-form';
      $form['field_area']['widget']['#description_display'] = 'before';
      $node = $form_state->getFormObject()->getEntity();
      // Show eDoc casefile ID field only if it has a value. This will
      // effectively disable the field for new hearings and leave it as it is
      // for existing hearings.
      $form['field_edoc_casefile_id']['#access'] = NULL !== $node
        && !empty($node->field_edoc_casefile_id->value);
    }
  }

}
