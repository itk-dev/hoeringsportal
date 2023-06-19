<?php

namespace Drupal\hoeringsportal_citizen_proposal\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\Entity\Node;

/**
 * Form for adding proposal.
 */
final class ProposalFormAdd extends ProposalFormBase {
  public const ADD_FORM_TITLE_MAXLENGTH = 80;
  public const ADD_FORM_PROPOSAL_MAXLENGTH = 2000;
  public const ADD_FORM_REMARKS_MAXLENGTH = 10000;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proposal_add_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getAuthenticateMessage(array $adminFormStateValues): string|TranslatableMarkup {
    return $adminFormStateValues['authenticate_message']['value'] ?? $this->t('You have to authenticate to add a proposal');
  }

  /**
   * {@inheritdoc}
   */
  public function buildProposalForm(array $form, FormStateInterface $formState): array {
    $defaltValues = $this->getDefaultFormValues();
    $adminFormStateValues = $this->getAdminFormStateValues();

    $form['author_intro'] = [
      '#type' => 'processed_text',
      '#format' => $adminFormStateValues['author_intro']['format'] ?? 'filtered_html',
      '#text' => $adminFormStateValues['author_intro']['value'] ?? '',
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this
        ->t('Name'),
      '#default_value' => $defaltValues['name'],
      '#attributes' => ['readonly' => TRUE],
      '#description' => $adminFormStateValues['name_help'] ?? '',
      '#description_display' => 'before',
    ];

    $form['email'] = [
      '#type' => 'email',
      '#required' => TRUE,
      '#title' => $this
        ->t('Email'),
      '#default_value' => $defaltValues['email'],
      '#description' => $adminFormStateValues['email_help'] ?? '',
      '#description_display' => 'before',
    ];

    $form['proposal_intro'] = [
      '#type' => 'processed_text',
      '#format' => $adminFormStateValues['proposal_intro']['format'] ?? 'filtered_html',
      '#text' => $adminFormStateValues['proposal_intro']['value'] ?? '',
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this
        ->t('Title'),
      '#description' => $adminFormStateValues['title_help'] ?? '',
      '#description_display' => 'before',
      '#default_value' => $defaltValues['title'],
      '#maxlength_js' => TRUE,
      '#attributes' => [
        'data-maxlength' => $this->getMaxLength('characters_title'),
        'maxlength_js_label' => $this->t('@remaining characters left.'),
      ],
    ];

    $form['proposal'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#rows' => 15,
      '#title' => $this
        ->t('Proposal'),
      '#description' => $adminFormStateValues['proposal_help'] ?? '',
      '#description_display' => 'before',
      '#default_value' => $defaltValues['proposal'],
      '#maxlength_js' => TRUE,
      '#attributes' => [
        'data-maxlength' => $this->getMaxLength('characters_proposal'),
        'maxlength_js_label' => $this->t('@remaining characters left.'),
      ],
    ];

    $form['remarks'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#rows' => 15,
      '#title' => $this
        ->t('Remarks'),
      '#description' => $adminFormStateValues['remarks_help'] ?? '',
      '#description_display' => 'before',
      '#default_value' => $defaltValues['remarks'],
      '#maxlength_js' => TRUE,
      '#attributes' => [
        'data-maxlength' => $this->getMaxLength('characters_remarks'),
        'maxlength_js_label' => $this->t('@remaining characters left.'),
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->helper->hasDraftProposal()
        ? $this->t('Update proposal')
        : $this->t('Create proposal'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if (strlen($form_state->getValue('title')) > $this->getMaxLength('characters_title')) {
      $form_state->setErrorByName('title', $this->t('Too many characters used.'));
    }

    if (strlen($form_state->getValue('proposal')) > $this->getMaxLength('characters_proposal')) {
      $form_state->setErrorByName('proposal', $this->t('Too many characters used.'));
    }

    if (strlen($form_state->getValue('remarks')) > $this->getMaxLength('characters_remarks')) {
      $form_state->setErrorByName('remarks', $this->t('Too many characters used.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $formState): void {
    $entity = Node::create([
      'type' => 'citizen_proposal',
      'title' => $formState->getValue('title'),
      'field_author_uuid' => $this->getUserUuid(),
      'field_author_name' => $formState->getValue('name'),
      'field_author_email' => $formState->getValue('email'),
      'field_proposal' => [
        'value' => $formState->getValue('proposal'),
        'format' => 'filtered_html',
      ],
      'field_remarks' => [
        'value' => $formState->getValue('remarks'),
        'format' => 'filtered_html',
      ],
    ]);
    $this->helper->setDraftProposal($entity);
    $formState
      ->setRedirect('hoeringsportal_citizen_proposal.citizen_proposal.proposal_approve');
  }

  /**
   * Get a number of characters from admin form or constant.
   *
   * @return int
   *   The calculated number of characters .
   */
  private function getMaxLength($adminFormElement): int {
    $adminFormStateValues = $this->getAdminFormStateValues();

    $constant = match ($adminFormElement) {
      'characters_title' => self::ADD_FORM_TITLE_MAXLENGTH,
      'characters_proposal' => self::ADD_FORM_PROPOSAL_MAXLENGTH,
      'characters_remarks' => self::ADD_FORM_REMARKS_MAXLENGTH,
      default => 0,
    };

    return !empty($adminFormStateValues[$adminFormElement]) ? (int) $adminFormStateValues[$adminFormElement] : $constant;
  }

}
