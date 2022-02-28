<?php

namespace Drupal\hoeringsportal_config_settings\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Hearing settings form.
 *
 * @package Drupal\itk_admin\Form
 */
class HearingSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hearing_settings';
  }

  /**
   * Get key/value storage for base config.
   *
   * @return object
   *   The base config.
   */
  private function getBaseConfig() {
    return \Drupal::getContainer()->get('itk_admin.itk_config');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->getBaseConfig();

    $form['hearing_warning_timer'] = [
      '#title' => $this->t('Hearing deadline timer'),
      '#type' => 'textfield',
      '#default_value' => $config->get('hearing_warning_timer'),
      '#weight' => '1',
      '#description' => $this->t('The number of hours before the deadline of a hearing the warning should appear.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save changes'),
      '#weight' => '6',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal\Core\Messenger\MessengerInterface::addMessage('Settings saved');

    // Set the rest of the configuration values.
    $this->getBaseConfig()->setMultiple([
      'hearing_warning_timer' => $form_state->getValue('hearing_warning_timer'),
    ]);

    drupal_flush_all_caches();
  }

}
