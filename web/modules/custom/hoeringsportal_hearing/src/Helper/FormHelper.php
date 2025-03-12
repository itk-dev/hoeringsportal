<?php

namespace Drupal\hoeringsportal_hearing\Helper;

use Drupal\Core\Datetime\DrupalDateTime;
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

      // Add our custom validation.
      $form['#validate'][] = [$this, 'validateForm'];
    }
  }

  /**
   * Form validation.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $form_state->getFormObject()->getEntity();

    // Validate dates on new hearings.
    if ($node->isNew() &&            'hearing' === $node->getType()) {
      $getDateTime = static function (string $field, bool $dateOnly = FALSE) use ($form_state): ?DrupalDateTime {
        /** @var ?DrupalDateTime $value */
        $value = $form_state->getValue($field)[0]['value'] ?? NULL;

        if ($value && $dateOnly) {
          // Create copy before removing time.
          $value = DrupalDateTime::createFromTimestamp($value->getTimestamp());
          $value->setTime(0, 0);
        }

        return $value;
      };

      $now = new DrupalDateTime();
      $startDate = $getDateTime('field_start_date');
      $replyDeadline = $getDateTime('field_reply_deadline');
      $deleteDate = $getDateTime('field_delete_date', dateOnly: TRUE);

      if ($startDate < $now) {
        $form_state->setErrorByName('field_start_date', $this->t('The start date must not be in the past'));
      }

      if ($replyDeadline < $now) {
        $form_state->setErrorByName('field_reply_deadline', $this->t('The reply deadline must not be in the past'));
      }
      elseif ($replyDeadline < $startDate) {
        $form_state->setErrorByName('field_reply_deadline', $this->t('The reply deadline must not be before the start date'));
      }

      if ($deleteDate < $now) {
        $form_state->setErrorByName('field_delete_date', $this->t('The delete date must not be in the past'));
      }
      elseif ($deleteDate < $replyDeadline) {
        $form_state->setErrorByName('field_delete_date', $this->t('The delete date must not be before the reply deadline'));
      }
    }
  }

}
