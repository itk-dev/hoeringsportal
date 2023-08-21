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
  protected function getAuthenticateMessage(): string|TranslatableMarkup {
    return $this->getAdminFormStateValue(
      ['authenticate_message', 'value'],
      $this->t('You have to authenticate to add a proposal')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildProposalForm(array $form, FormStateInterface $formState): array {
    $defaltValues = $this->getDefaultFormValues();

    $form['author_intro_container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mt-5']],
    ];

    $form['author_intro_container']['author_intro'] = [
      '#type' => 'processed_text',
      '#format' => $this->getAdminFormStateValue(['author_intro', 'format'], 'filtered_html'),
      '#text' => $this->getAdminFormStateValue(['author_intro', 'value'], ''),
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this
        ->t('Name'),
      '#default_value' => $defaltValues['name'],
      '#attributes' => ['readonly' => TRUE, 'class' => ['mb-3']],
      '#description' => $this->getAdminFormStateValue('name_help'),
      '#description_display' => 'before',
    ];

    $form['phone'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this
        ->t('Phone'),
      '#default_value' => $defaltValues['phone'],
      '#attributes' => ['class' => ['mb-3']],
      '#description' => $this->getAdminFormStateValue('phone_help'),
      '#description_display' => 'before',
    ];

    $form['email'] = [
      '#type' => 'email',
      '#required' => TRUE,
      '#title' => $this
        ->t('Email'),
      '#default_value' => $defaltValues['email'],
      '#description' => $this->getAdminFormStateValue('email_help'),
      '#description_display' => 'before',
    ];

    $form['email_display'] = [
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Display email'),
      '#default_value' => $defaltValues['email_display'] ?? TRUE,
      '#description' => $this->getAdminFormStateValue('email_display_help'),
      '#description_display' => 'before',
    ];

    $form['proposal_intro_container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['mt-5']],
    ];

    $form['proposal_intro_container']['proposal_intro'] = [
      '#type' => 'processed_text',
      '#format' => $this->getAdminFormStateValue(['proposal_intro', 'format'], 'filtered_html'),
      '#text' => $this->getAdminFormStateValue(['proposal_intro', 'value'], ''),
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this
        ->t('Title'),
      '#description' => $this->getAdminFormStateValue('title_help'),
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
      '#description' => $this->getAdminFormStateValue('proposal_help'),
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
      '#description' => $this->getAdminFormStateValue('remarks_help'),
      '#description_display' => 'before',
      '#default_value' => $defaltValues['remarks'],
      '#maxlength_js' => TRUE,
      '#attributes' => [
        'data-maxlength' => $this->getMaxLength('characters_remarks'),
        'maxlength_js_label' => $this->t('@remaining characters left.'),
      ],
    ];

    $this->buildSurveyForm($form);

    $form['consent'] = [
      '#type' => 'checkbox',
      '#title' => $this
        ->t('Personal data storage consent'),
      '#required' => TRUE,
      '#default_value' => FALSE,
      '#description' => $this->getAdminFormStateValue('consent_help'),
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
   * Build survey form.
   *
   * @param array $form
   *   The form.
   *
   * @return array
   *   The form.
   */
  private function buildSurveyForm(array &$form): array {
    $form['survey'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['survey', 'citizen-proposal-survey'],
      ],
      '#tree' => TRUE,
    ];

    try {
      $description = $this->getAdminFormStateValue(['survey', 'description']);
      if (($webform = $this->loadSurvey()) && isset($description['value'])) {
        // We use a numeric index (implicit 0) here to prevent webform fields
        // accidentally overwriting the description element.
        $form['survey'][] = [
          '#type' => 'processed_text',
          '#text' => $description['value'],
          '#format' => $description['format'] ?? 'filtered_html',
        ];

        $this->webformHelper->renderWebformElements($webform, $form['survey']);
      }
    }
    catch (\Exception $exception) {
      throw $exception;
    }

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
      'field_author_phone' => $formState->getValue('phone'),
      'field_author_email' => $formState->getValue('email'),
      'field_author_email_display' => $formState->getValue('email_display'),
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

    // Handle survey.
    try {
      if ($webform = $this->loadSurvey()) {
        $surveyData = (array) $formState->getValue('survey');
        $this->webformHelper->setSurveyResponse($webform, $surveyData);
      }
    }
    catch (\Exception) {
    }
  }

  /**
   * Get a number of characters from admin form or constant.
   *
   * @return int
   *   The calculated number of characters.
   */
  private function getMaxLength($adminFormElement): int {
    $value = (int) $this->getAdminFormStateValue($adminFormElement);
    if ($value > 0) {
      return $value;
    }

    return match ($adminFormElement) {
      'characters_title' => self::ADD_FORM_TITLE_MAXLENGTH,
      'characters_proposal' => self::ADD_FORM_PROPOSAL_MAXLENGTH,
      'characters_remarks' => self::ADD_FORM_REMARKS_MAXLENGTH,
      default => 0,
    };
  }

}
