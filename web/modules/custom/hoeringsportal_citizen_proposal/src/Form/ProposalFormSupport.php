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
  protected function getAuthenticateMessage(): string|TranslatableMarkup {
    return $this->getAdminFormStateValue(
      ['authenticate_support_message', 'value'],
      $this->t('You have to authenticate to support a proposal')
    );
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

    if ($this->isAuthenticatedAsCitizen()) {
      $supportedAt = $this->helper->getUserSupportedAt($this->getUserUuid(), $node);
      if (NULL !== $supportedAt) {
        $form['message'] = [
          '#markup' => $this->t('You already supported this proposal on @support_date. You can only support a proposal once.',
            [
              '@support_date' => $supportedAt->format('d/m/Y'),
            ]),
        ];
        return $form;
      }
    }
    elseif ($this->isAuthenticatedAsEditor()) {
      $form['message'] = [
        '#theme' => 'status_messages',
        '#message_list' => [
          'warning' => [$this->t("You're supporting @label on behalf of a citizen", [
            '@label' => $node->label(),
          ]),
          ],
        ],
      ];
    }
    else {
      return [];
    }

    $defaltValues = $this->getDefaultFormValues();

    $form['support_intro'] = [
      '#type' => 'processed_text',
      '#format' => $this->getAdminFormStateValue(['support_intro', 'format'], 'filtered_html'),
      '#text' => $this->getAdminFormStateValue(['support_intro', 'value'], ''),

    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this
        ->t('Name'),
      '#default_value' => $defaltValues['name'],
      '#attributes' => ['readonly' => !$this->isAuthenticatedAsEditor()],
      '#description' => $this->getAdminFormStateValue('support_name_help'),
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
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $form['#node'];

    if (!$this->isAuthenticated()) {
      $form_state
        ->setRedirect('hoeringsportal_citizen_proposal.support', ['node' => $node->id()]);
      return;
    }

    try {
      $this->helper->saveSupport(
        $this->getUserUuid(),
        $node,
        [
          'user_name' => $form_state->getValue('name'),
          'created' => time(),
        ],
      );
      $this->messenger()->addStatus($this->getAdminFormStateValue('support_submission_text', $this->t('Thank you for your support.')));
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('Something went wrong. Your support was not registered.'));
    }

    $this->deAuthenticateUser();
    $form_state
      ->setRedirect('entity.node.canonical', ['node' => $node->id()]);
  }

}
