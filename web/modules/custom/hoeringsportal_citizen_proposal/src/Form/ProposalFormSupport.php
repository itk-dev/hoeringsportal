<?php

namespace Drupal\hoeringsportal_citizen_proposal\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Form for supporting proposal.
 */
final class ProposalFormSupport extends ProposalFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proposal_support_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getAuthenticateMessage(array $adminFormStateValues): string|TranslatableMarkup {
    return $adminFormStateValues['authenticate_support_message']['value'] ?? $this->t('You have to authenticate to support a proposal');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = NULL): RedirectResponse|array {
    // Pass the node to the submit handler.
    $form['#node'] = $node;

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function buildProposalForm(array $form, FormStateInterface $formState): array|RedirectResponse {
    $node = $form['#node'];
    assert($node instanceof NodeInterface);

    $supportedAt = $this->helper->getUserSupportedAt($this->getUserUuid(), $node);
    if (NULL !== $supportedAt) {
      $form['message'] = [
        '#markup' => $this->t('You already supported this proposal on @support_date. You can only support a proposal once.', [
          '@support_date' => $supportedAt->format('d/m/Y'),
        ]),
      ];
      return $form;
    }

    $defaltValues = $this->getDefaultFormValues();
    $adminFormStateValues = $this->getAdminFormStateValues();

    $form['support_intro'] = [
      '#type' => 'processed_text',
      '#format' => $adminFormStateValues['support_intro']['format'] ?? 'filtered_html',
      '#text' => $adminFormStateValues['support_intro']['value'] ?? '',
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this
        ->t('Name'),
      '#default_value' => $defaltValues['name'],
      '#attributes' => ['readonly' => TRUE],
      '#description' => $adminFormStateValues['support_name_help'] ?? '',
      '#description_display' => 'before',
    ];

    $form['email'] = [
      '#type' => 'email',
      '#required' => TRUE,
      '#title' => $this
        ->t('Email'),
      '#default_value' => $defaltValues['email'],
      '#description' => $adminFormStateValues['support_email_help'] ?? '',
      '#description_display' => 'before',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Support proposal'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultFormValues(): array {
    $userData = $this->getUserData();

    return [
      'name' => $userData['name'] ?? NULL,
      'email' => $userData['email'] ?? NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $userData = $this->getUserData();
    $adminFormStateValues = $this->getAdminFormStateValues();

    /** @var \Drupal\node\NodeInterface $node */
    $node = $form['#node'];

    if (empty($userData)) {
      $form_state
        ->setRedirect('hoeringsportal_citizen_proposal.support', ['node' => $node->id()]);
      return;
    }

    $this->helper->saveSupport(
      $this->getUserUuid(),
      $node,
      [
        'user_name' => $form_state->getValue('name'),
        'user_email' => $form_state->getValue('email'),
        'created' => time(),
      ],
      $adminFormStateValues['support_submission_text']
    );

    $form_state
      ->setRedirect('entity.node.canonical', ['node' => $node->id()]);
  }

}
