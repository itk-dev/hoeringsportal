<?php

namespace Drupal\hoeringsportal_citizen_proposal\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Form for approving proposal.
 */
final class ProposalFormApprove extends ProposalFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proposal_approve_form';
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
  public function buildProposalForm(array $form, FormStateInterface $formState): array|RedirectResponse {
    if (!$this->helper->hasDraftProposal()) {
      return $this->abandonSubmission();
    }

    $defaltValues = $this->getDefaultFormValues();
    $adminFormStateValues = $this->getAdminFormStateValues();

    $form['approve_form_help'] = [
      '#type' => 'processed_text',
      '#format' => $adminFormStateValues['approve_intro']['format'] ?? 'filtered_html',
      '#text' => $adminFormStateValues['approve_intro']['value'] ?? '',
    ];

    $form['author'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'd-flex', 'justify-content-between', 'mt-3', 'border-bottom', 'pb-3',
        ],
      ],
    ];

    $form['author']['name_wrapper'] = [
      '#type' => 'container',
    ];

    $form['author']['name_wrapper']['approve_form_name'] = [
      '#prefix' => '<h5>' . $this->t('Name') . '</h5>',
      '#type' => 'processed_text',
      '#text' => $defaltValues['name'],
      '#format' => 'filtered_html',
    ];

    $form['author']['email_wrapper'] = [
      '#type' => 'container',
    ];

    $emailHiddenText = $defaltValues['email_display'] ? '' : '<small><strong>(Hidden)</strong></small>';
    $form['author']['email_wrapper']['approve_form_email'] = [
      '#prefix' => '<h5>' . $this->t('E-mail') . '</h5>',
      '#type' => 'processed_text',
      '#text' => $defaltValues['email'],
      '#format' => 'filtered_html',
      '#suffix' => $emailHiddenText,
    ];

    $form['author']['phone_display_wrapper'] = [
      '#type' => 'container',
    ];

    $form['author']['phone_display_wrapper']['approve_form_phone'] = [
      '#prefix' => '<h5>' . $this->t('Phone number') . '</h5>',
      '#type' => 'processed_text',
      '#text' => $defaltValues['phone'],
      '#format' => 'filtered_html',
      '#suffix' => '<small><strong>(Hidden)</strong></small>',
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
      return $this->abandonSubmission();
    }

    $adminFormStateValues = $this->getAdminFormStateValues();
    $this->applyRedirect($adminFormStateValues['approve_goto_url'], $formState);

    $this->messenger()->addStatus($adminFormStateValues['approve_submission_text'] ?? '');
    $entity->save();
    $this->helper->deleteDraftProposal();
  }

  /**
   * Custom submit handler for cancelling a submission.
   */
  public function cancelSubmit(array &$form, FormStateInterface $formState) {
    $this->messenger()->addStatus($this->t('Your submission has been cancelled.'));
    $this->helper->deleteDraftProposal();

    $adminFormStateValues = $this->getAdminFormStateValues();
    $this->applyRedirect($adminFormStateValues['approve_goto_url'], $formState);
  }

  /**
   * Abandon submission and add redirect response to form state.
   */
  private function abandonSubmission() {
    $this->messenger()->addWarning($this->t('Could not find a proposal to approve.'));
    $url = Url::fromRoute('hoeringsportal_citizen_proposal.citizen_proposal.proposal_add');

    return new RedirectResponse($url->toString());
  }

  /**
   * Add redirect to form state.
   *
   * @param string $inputUrl
   *   The url added from admin interface.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The state of the form.
   *
   * @return \Drupal\Core\Form\FormStateInterface
   *   The modified form.
   */
  private function applyRedirect(string $inputUrl, FormStateInterface &$formState): FormStateInterface {
    $parsedUrl = parse_url($inputUrl);

    try {
      if (empty($parsedUrl['path'])) {
        $formState->setRedirect('<front>');

        return $formState;
      }
      $url = URL::fromUserInput($parsedUrl['path']);
    }
    catch (\Exception) {
      $formState->setRedirect('<front>');

      return $formState;
    }

    if ($url->isExternal() || !$url->isRouted()) {
      $formState->setRedirect('<front>');
    }
    else {
      $routeName = $url->getRouteName();
      $routeParameters = $url->getRouteParameters();
      $formState->setRedirect($routeName, $routeParameters);
    }

    return $formState;
  }

}
