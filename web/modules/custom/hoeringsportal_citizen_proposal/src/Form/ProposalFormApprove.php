<?php

namespace Drupal\hoeringsportal_citizen_proposal\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form for approving proposal.
 */
final class ProposalFormApprove extends ProposalFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proposal_form_approve';
  }

  /**
   * {@inheritdoc}
   */
  public function buildProposalForm(array $form, FormStateInterface $formState): array {
    if (!$this->helper->hasDraftProposal()) {
      return $this->abandonSubmission($formState);
    }

    $defaltValues = $this->getDefaultFormValues();
    $adminFormStateValues = $this->getAdminFormStateValues();

    $form['approve_form_help'] = [
      '#type' => 'processed_text',
      '#format' => $adminFormStateValues['approve_intro']['format'] ?? 'filtered_html',
      '#text' => $adminFormStateValues['approve_intro']['value'] ?? '',
    ];

    $form['approve_form_title'] = [
      '#prefix' => '<h5>' . $this->t('Title') . '</h5>',
      '#type' => 'processed_text',
      '#text' => $defaltValues['title'],
      '#format' => 'filtered_html',
    ];

    $form['approve_form_proposal'] = [
      '#prefix' => '<h5>' . $this->t('Proposal') . '</h5>',
      '#type' => 'processed_text',
      '#text' => $defaltValues['proposal'],
      '#format' => 'filtered_html',
    ];

    $form['approve_form_remarks'] = [
      '#prefix' => '<h5>' . $this->t('Remarks') . '</h5>',
      '#type' => 'processed_text',
      '#text' => $defaltValues['remarks'],
      '#format' => 'filtered_html',
    ];

    $form['approve_form_name'] = [
      '#prefix' => '<h5>' . $this->t('Name') . '</h5>',
      '#type' => 'processed_text',
      '#text' => $defaltValues['name'],
      '#format' => 'filtered_html',
    ];

    $form['approve_form_email'] = [
      '#prefix' => '<h5>' . $this->t('E-mail') . '</h5>',
      '#type' => 'processed_text',
      '#text' => $defaltValues['email'],
      '#format' => 'filtered_html',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Approve proposal'),
      '#button_type' => 'primary',
    ];

    $form['actions']['edit_link'] = [
      '#title' => $this->t('Edit proposal'),
      '#type' => 'link',
      '#url' => Url::fromRoute('hoeringsportal_citizen_proposal.citizen_proposal.proposal_add'),
      '#attributes' => ['class' => ['btn', 'btn-secondary', 'mb-2']],
    ];

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel proposal'),
      '#button_type' => 'link',
      '#submit' => [$this->cancelSubmit(...)],
      '#attributes' => ['class' => ['btn', 'btn-link']],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $formState) {
    if (!$entity = $this->helper->getDraftProposal()) {
      $this->abandonSubmission($formState);
      return;
    }

    $this->messenger()->addStatus($this->t('Thank you for your submission.'));
    $entity->save();
    $this->helper->deleteDraftProposal();
    $formState->setRedirect('<front>');
  }

  /**
   * Custom submit handler for cancelling a submission.
   */
  public function cancelSubmit(array &$form, FormStateInterface $formState) {
    $this->messenger()->addStatus($this->t('Your submission has been cancelled.'));
    $this->helper->deleteDraftProposal();
    $formState->setRedirect('<front>');
  }

  /**
   * Abandon submission and add redirect response to form state.
   */
  private function abandonSubmission(FormStateInterface $formState) {
    $this->messenger()->addWarning($this->t('Could not find a proposal to approve.'));

    $formState->setRedirect('hoeringsportal_citizen_proposal.citizen_proposal.proposal_add');
  }

}
