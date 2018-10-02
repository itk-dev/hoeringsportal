<?php

namespace Drupal\hoeringsportal_deskpro\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form form definition for adding a hearing.
 */
class HearingAddForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hearing_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form_config = \Drupal::getContainer()->get('hoeringsportal_deskpro.form_config');
    $file_validators = [
      'file_validate_size' => [10490000],
    ];

    $form['hearing_intro_text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $form_config->get('intro'),
    ];

    $form['hearing_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
    ];

    $form['hearing_email'] = [
      '#type' => 'email',
      '#title' => $this->t('E-mail'),
      '#required' => TRUE,
    ];

    $form['hearing_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address'),
      '#states' => [
        'visible' => [
          ':input[name="hearing_address_secret"]' => ['checked' => FALSE],
        ],
        'required' => [
          ':input[name="hearing_address_secret"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['hearing_address_secret'] = [
      '#type' => 'checkbox',
      '#title' => $this
        ->t('My address is secret'),
    ];

    $form['hearing_representation'] = [
      '#type' => 'select',
      '#title' => $this->t('I represent'),
      '#options' => $form_config->get('representation'),
      '#required' => TRUE,
    ];

    $form['hearing_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#required' => TRUE,
    ];

    $form['hearing_message'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Message'),
      '#format' => 'filtered_html',
      '#required' => TRUE,
    ];

    $form['hearing_file'] = [
      '#title' => $this->t('Select a file'),
      '#type' => 'managed_file',
      '#upload_validators' => $file_validators,
      '#description' => [
        '#theme' => 'file_upload_help',
        '#upload_validators' => $file_validators,
      ],
      '#multiple' => TRUE,
    ];

    $form['hearing_consent_text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $form_config->get('consent'),
    ];

    $form['hearing_consent'] = [
      '#type' => 'checkbox',
      '#title' => $this
        ->t('I have red the terms and give my consent'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message($this->t('Your hearing has being submitted!'));
  }

}
