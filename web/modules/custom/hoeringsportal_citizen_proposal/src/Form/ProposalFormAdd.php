<?php

namespace Drupal\hoeringsportal_citizen_proposal\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\Entity\Node;

/**
 * Form for adding proposal.
 */
final class ProposalFormAdd extends ProposalFormBase {

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
  public function submitForm(array &$form, FormStateInterface $formState): void {
    // @todo add real UUID.
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

}
