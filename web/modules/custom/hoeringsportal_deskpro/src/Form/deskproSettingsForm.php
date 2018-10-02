<?php

namespace Drupal\hoeringsportal_deskpro\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

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
   * @return object
   *   The config object.
   */
  private function getFormConfig() {
    return \Drupal::getContainer()->get('hoeringsportal_deskpro.form_config');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $description_representation = '<p>' . t('Enter the possible values this field can contain. Enter one value per line, in the format key|label.');
    $description_representation .= '<br/>' . t('The key is the value used by Deskpro, and must be numeric. The label will be used when  showing the dropdown list.');
    $description_representation .= '</p>';

    $config = $this->getFormConfig();

    $representation = $config->get('representation');
    foreach ($representation as $key => $value) {
      $lines[] = $key . '|' . $value;
    }
    $representation_value = implode(PHP_EOL, $lines);

    $form['add_hearing_form'] = [
      '#type' => 'details',
      '#title' => $this->t('Add hearing form'),
      '#open' => TRUE,
    ];

    $form['add_hearing_form']['intro'] = [
      '#title' => $this->t('Intro text'),
      '#type' => 'text_format',
      '#format' => 'filtered_html',
      '#default_value' => $config->get('intro'),
      '#weight' => '1',
      '#size' => 60,
    ];

    $form['add_hearing_form']['consent'] = [
      '#title' => $this->t('Consent text'),
      '#type' => 'text_format',
      '#format' => 'filtered_html',
      '#default_value' => $config->get('consent'),
      '#weight' => '1',
      '#size' => 60,
    ];

    $form['representation'] = [
      '#type' => 'textarea',
      '#title' => $this
        ->t('Representation list'),
      '#description' => $description_representation,
      '#default_value' => $representation_value,
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
    drupal_set_message($this->t('Your settings have been saved'));
    $options = [];
    $representation = $form_state->getValue('representation');
    $lines = explode(PHP_EOL, $representation);
    foreach ($lines as $line) {
      $line = str_replace("\r", '', $line);
      $line = explode('|', $line);
      $options[$line[0]] = $line[1];
    }

    // Set the configuration values.
    $this->getFormConfig()->setMultiple([
      'intro' => $form_state->getValue('intro')['value'],
      'consent' => $form_state->getValue('consent')['value'],
      'representation' => $options,
    ]);

    drupal_flush_all_caches();
  }

}
