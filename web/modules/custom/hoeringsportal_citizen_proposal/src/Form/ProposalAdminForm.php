<?php

namespace Drupal\hoeringsportal_citizen_proposal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;
use Drupal\hoeringsportal_citizen_proposal\Helper\MailHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for adding proposal.
 */
final class ProposalAdminForm extends FormBase {

  /**
   * Constructor for the proposal add form.
   */
  public function __construct(
    readonly private Helper $helper
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(Helper::class),
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
    $adminFormStateValues = $this->helper->getAdminValue();

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

    $form['add_form']['consent_help'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consent help'),
      '#default_value' => $adminFormStateValues['consent_help'] ?? '',
      '#description' => $this->t('Use <code>&lt;a target=&quot;_blank&quot; href=&quot;…&quot;&gt;Read more about GDRP and data storage&lt;/a&gt;</code> to insert a link to details on what is stored and where and how.'),
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
      '#title' => $this->t('Redirect URL after a proposal has been submitted'),
      '#default_value' => $adminFormStateValues['approve_goto_url'] ?? '',
    ];

    $form['approve_form']['approve_submission_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Submission text when a proposal has been submitted'),
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

    $form['support_form']['support_goto_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Redirect URL after a proposal has been supported'),
      '#default_value' => $adminFormStateValues['support_goto_url'] ?? '',
      '#description' => $this->t('If not set, the citizen will see the proposal after supporting it.'),
    ];

    $form['support_form']['support_submission_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Submission text when a proposal has been supported'),
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

    $this->buildEmailsForm($form, $adminFormStateValues ?? []);

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Build emails form.
   *
   * @param array $form
   *   The form.
   * @param array $adminFormStateValues
   *   The admin form state values.
   *
   * @return array
   *   The form.
   */
  private function buildEmailsForm(array &$form, array $adminFormStateValues): array {
    $form['emails'] = [
      '#type' => 'details',
      '#tree' => TRUE,
      '#open' => TRUE,
      '#title' => $this
        ->t('Emails'),
    ];

    $form['emails']['description'] = [
      '#markup' => $this->t('You can use tokens in email subject and both tokens and Twig in email content.'),
    ];

    $form['emails']['email_editor'] = [
      '#type' => 'email',
      '#title' => $this->t('Editor email address'),
      '#required' => TRUE,
      '#default_value' => $adminFormStateValues['emails']['email_editor'] ?? '',
    ];

    foreach ([
      MailHelper::MAILER_SUBTYPE_PROPOSAL_CREATED_CITIZEN => $this->t('Proposal created (citizen)'),
      MailHelper::MAILER_SUBTYPE_PROPOSAL_CREATED_EDITOR => $this->t('Proposal created (editor)'),
      MailHelper::MAILER_SUBTYPE_PROPOSAL_PUBLISHED_CITIZEN => $this->t('Proposal published (citizen)'),
    ] as $key => $title) {
      $form['emails'][$key] = [
        '#type' => 'fieldset',
        '#title' => $title,

        'subject' => [
          '#type' => 'textfield',
          '#title' => $this->t('Subject'),
          '#required' => TRUE,
          '#default_value' => $adminFormStateValues['emails'][$key]['subject'] ?? '',
        ],
        'content' => [
          '#type' => 'text_format',
          '#title' => $this->t('Content'),
          '#required' => TRUE,
          '#format' => $adminFormStateValues['emails'][$key]['content']['format'] ?? 'email_html',
          '#default_value' => $adminFormStateValues['emails'][$key]['content']['value'] ?? '',
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $formState): void {
    $this->helper->setAdminValues($formState->getValues());
  }

}