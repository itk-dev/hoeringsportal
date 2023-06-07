<?php

namespace Drupal\hoeringsportal_citizen_proposal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\State;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for adding proposal.
 */
final class ProposalAdminForm extends FormBase {

  /**
   * Constructor for the proposal add form.
   */
  public function __construct(
    readonly private State $state,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proposal_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $adminFormStateValues = $this->state->get('citizen_proposal_admin_form_values');
    $form['add_form'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this
        ->t('Add form'),
    ];

    $form['add_form']['author_intro'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Author intro'),
      '#format' => $adminFormStateValues['author_intro']['format'] ?? 'filtered_html',
      '#default_value' => $adminFormStateValues['author_intro']['value'] ?? '',
    ];

    $form['add_form']['name_help'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name help'),
      '#default_value' => $adminFormStateValues['name_help'] ?? '',
    ];

    $form['add_form']['email_help'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email help'),
      '#default_value' => $adminFormStateValues['email_help'] ?? '',
    ];

    $form['add_form']['proposal_intro'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Proposal intro'),
      '#format' => $adminFormStateValues['proposal_intro']['format'] ?? 'filtered_html',
      '#default_value' => $adminFormStateValues['proposal_intro']['value'] ?? '',
    ];

    $form['add_form']['title_help'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title help'),
      '#default_value' => $adminFormStateValues['title_help'] ?? '',
    ];

    $form['add_form']['proposal_help'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Proposal help'),
      '#default_value' => $adminFormStateValues['proposal_help'] ?? '',
    ];

    $form['add_form']['remarks_help'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Remarks help'),
      '#default_value' => $adminFormStateValues['remarks_help'] ?? '',
    ];

    $form['approve_form'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this
        ->t('Approve form'),
    ];

    $form['approve_form']['approve_intro'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Approve intro'),
      '#format' => $adminFormStateValues['approve_intro']['format'] ?? 'filtered_html',
      '#default_value' => $adminFormStateValues['approve_intro']['value'] ?? '',
    ];

    $form['sidebar'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this
        ->t('Sidebar'),
    ];

    $form['sidebar']['sidebar_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Sidebar text'),
      '#format' => $adminFormStateValues['sidebar_text']['format'] ?? 'filtered_html',
      '#default_value' => $adminFormStateValues['sidebar_text']['value'] ?? '',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->state->set('citizen_proposal_admin_form_values', $form_state->getValues());
  }

}
