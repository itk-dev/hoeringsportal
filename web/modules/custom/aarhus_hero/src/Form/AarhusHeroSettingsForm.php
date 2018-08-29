<?php

namespace Drupal\aarhus_hero\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Class AarhusHeroSettingsForm.
 *
 * @package Drupal\aarhus_hero\Form
 */
class AarhusHeroSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aarhus_hero_settings';
  }

  /**
   * Get key/value storage for base config.
   *
   * @return object
   *   The config object.
   */
  private function getBaseConfig() {
    return \Drupal::getContainer()->get('aarhus_hero.hero_config');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->getBaseConfig();

    $form['hero'] = [
      '#type' => 'details',
      '#title' => $this->t('Hero'),
      '#open' => TRUE,
    ];

    $form['hero']['hero_title'] = [
      '#title' => $this->t('Hero title'),
      '#type' => 'textfield',
      '#default_value' => $config->get('hero_title'),
      '#weight' => '1',
      '#size' => 60,
      '#maxlength' => 128,
    ];

    $form['hero']['hero_text'] = [
      '#title' => $this->t('Hero text'),
      '#type' => 'textfield',
      '#default_value' => $config->get('hero_text'),
      '#weight' => '2',
      '#size' => 60,
      '#maxlength' => 128,
    ];

    $form['hero']['hero_image'] = [
      '#title' => $this->t('Hero image'),
      '#type' => 'managed_file',
      '#default_value' => !empty($config->get('hero_image')) ? [$config->get('hero_image')] : NULL,
      '#upload_location' => 'public://',
      '#weight' => '3',
      '#open' => TRUE,
    ];

    $form['hero']['hero_button_text'] = [
      '#title' => $this->t('Hero button text'),
      '#type' => 'textfield',
      '#default_value' => $config->get('hero_button_text'),
      '#weight' => '2',
      '#size' => 60,
      '#maxlength' => 128,
    ];

    $form['hero']['hero_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL'),
      '#default_value' => $config->get('hero_url'),
      '#weight' => '4',
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
    // Load the file set in the form.
    $value = !empty($form_state->getValue('hero_image')[0]) ? $form_state->getValue('hero_image')[0] : NULL;
    $file = ($value) ? File::load($value) : FALSE;

    // If a file is set.
    if ($file) {
      // Add file to file_usage table.
      \Drupal::service('file.usage')->add($file, 'aarhus_hero', 'user', '1');
    }
    // Set the configuration values.
    $this->getBaseConfig()->setMultiple([
      'hero_title' => $form_state->getValue('hero_title'),
      'hero_url' => $form_state->getValue('hero_url'),
      'hero_text' => $form_state->getValue('hero_text'),
      'hero_button_text' => $form_state->getValue('hero_button_text'),
      'hero_image' => !empty($form_state->getValue('hero_image')[0]) ? $form_state->getValue('hero_image')[0] : NULL,
    ]);

    drupal_flush_all_caches();
  }

}
