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
  public const ADMIN_FORM_VALUES_STATE_KEY = 'citizen_proposal_admin_form_values';

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
    $adminFormStateValues = $this->state->get(self::ADMIN_FORM_VALUES_STATE_KEY);

    $form['authenticate'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this
        ->t('Authenticate'),
    ];

    $form['authenticate']['authenticate_message'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Authenticate message'),
      '#format' => $adminFormStateValues['authenticate_message']['format'] ?? 'filtered_html',
      '#default_value' => $adminFormStateValues['authenticate_message']['value'] ?? '',
    ];

    $form['authenticate']['authenticate_support_message'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Authenticate message (support)'),
      '#format' => $adminFormStateValues['authenticate_support_message']['format'] ?? 'filtered_html',
      '#default_value' => $adminFormStateValues['authenticate_support_message']['value'] ?? '',
    ];

    $form['authenticate']['authenticate_link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Authenticate link text'),
      '#default_value' => $adminFormStateValues['authenticate_link_text'] ?? '',
    ];

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

    $form['add_form']['phone_help'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone help'),
      '#default_value' => $adminFormStateValues['phone_help'] ?? '',
    ];

    $form['add_form']['email_help'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email help'),
      '#default_value' => $adminFormStateValues['email_help'] ?? '',
    ];

    $form['add_form']['email_display_help'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email display help'),
      '#default_value' => $adminFormStateValues['email_display_help'] ?? '',
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

    $form['add_form']['characters_title'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of allowed characters for title field.'),
      '#default_value' => $adminFormStateValues['characters_title'] ?? '',
    ];

    $form['add_form']['characters_proposal'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of allowed characters for proposal field.'),
      '#default_value' => $adminFormStateValues['characters_proposal'] ?? '',
    ];

    $form['add_form']['characters_remarks'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of allowed characters for remarks field.'),
      '#default_value' => $adminFormStateValues['characters_remarks'] ?? '',
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

    $form['approve_form']['approve_goto_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Goto this url after submission'),
      '#default_value' => $adminFormStateValues['approve_goto_url'] ?? '',
    ];

    $form['approve_form']['approve_submission_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Submission text when the proposal is approved.'),
      '#default_value' => $adminFormStateValues['approve_submission_text'] ?? '',
    ];

    $form['support_form'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this
        ->t('Support form'),
    ];

    $form['support_form']['support_intro'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Support intro'),
      '#format' => $adminFormStateValues['support_intro']['format'] ?? 'filtered_html',
      '#default_value' => $adminFormStateValues['support_intro']['value'] ?? '',
    ];

    $form['support_form']['support_name_help'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name help'),
      '#default_value' => $adminFormStateValues['support_name_help'] ?? '',
    ];

    $form['support_form']['support_email_help'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email help'),
      '#default_value' => $adminFormStateValues['support_email_help'] ?? '',
    ];

    $form['support_form']['support_submission_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Submission text when the proposal has been supported.'),
      '#default_value' => $adminFormStateValues['support_submission_text'] ?? '',
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
  public function submitForm(array &$form, FormStateInterface $formState): void {
    $this->state->set(self::ADMIN_FORM_VALUES_STATE_KEY, $formState->getValues());
  }

}
