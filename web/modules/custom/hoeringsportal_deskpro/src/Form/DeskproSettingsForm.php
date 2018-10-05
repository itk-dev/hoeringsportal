<?php

namespace Drupal\hoeringsportal_deskpro\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;

/**
 * Form definition for applying configuration for Deskpro module in UI.
 */
class DeskproSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'deskpro_settings_form';
  }

  /**
   * Get key/value storage for base config.
   *
   * @return \Drupal\hoeringsportal_deskpro\State\AddHearingTicketFormConfig
   *   The config object.
   */
  private function getFormConfig() {
    return \Drupal::getContainer()->get('hoeringsportal_deskpro.form_config');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->getFormConfig();

    $form['deskpro_settings'] = [
      '#type' => 'vertical_tabs',
      '#default_tab' => 'edit-add-hearing-form',
    ];

    $form['add_hearing_ticket_form'] = [
      '#type' => 'details',
      '#title' => $this->t('Add hearing ticket form'),
      '#open' => TRUE,
      '#group' => 'deskpro_settings',
    ];

    $form['add_hearing_ticket_form']['intro'] = [
      '#title' => $this->t('Intro text'),
      '#type' => 'text_format',
      '#format' => 'filtered_html',
      '#default_value' => $config->get('intro'),
      '#weight' => '1',
      '#size' => 60,
    ];

    $form['add_hearing_ticket_form']['consent'] = [
      '#title' => $this->t('Consent text'),
      '#type' => 'text_format',
      '#format' => 'filtered_html',
      '#default_value' => $config->get('consent'),
      '#weight' => '2',
      '#size' => 60,
    ];

    $form['add_hearing_ticket_form']['ticket_created_confirmation'] = [
      '#title' => $this->t('Confirmation after ticket submit'),
      '#type' => 'text_format',
      '#format' => 'filtered_html',
      '#default_value' => $config->get('ticket_created_confirmation'),
      '#weight' => '2',
      '#size' => 60,
    ];

    $example = <<<'YAML'
5: Privatperson
3: Virksomhed
4:
 label: Forening/organisation
 require_organization: true
20: Råd og nævn
21: Myndighed
YAML;

    $description = implode([
      '<p>', t('Define representations here to match your Deskpro setup.'), '</p>',
      '<p>Example</p>',
      '<pre>', Yaml::encode(Yaml::decode($example)), '</pre>',
    ]);

    $form['add_hearing_ticket_form']['representations'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Representation list'),
      '#rows' => 10,
      '#description' => $description,
      '#default_value' => $config->get('representations'),
      '#weight' => '3',
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    try {
      $value = $form_state->getValue('representations');
      $this->getFormConfig()->validateRepresentations($value);
    }
    catch (\Exception $exception) {
      $form_state->setErrorByName('representations', $this->t('Invalid YAML'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Set the configuration values.
    $this->getFormConfig()->setMultiple([
      'intro' => $form_state->getValue('intro')['value'],
      'consent' => $form_state->getValue('consent')['value'],
      'representations' => $form_state->getValue('representations'),
      'ticket_created_confirmation' => $form_state->getValue('ticket_created_confirmation')['value'],
    ]);

    drupal_flush_all_caches();

    $this->messenger()->addMessage($this->t('Your settings have been saved.'));
  }

}
