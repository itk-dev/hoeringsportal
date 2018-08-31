<?php

namespace Drupal\hoeringsportal_config_settings\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Class ItkGeneralSettingsForm.
 *
 * @package Drupal\itk_admin\Form
 */
class ItkGeneralSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'itk_general_settings';
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

    $form['footer_text'] = [
      '#title' => $this->t('Footer text'),
      '#type' => 'text_format',
      '#format' => 'filtered_html',
      '#default_value' => $config->get('footer_text'),
      '#weight' => '1',
    ];

    $form['overview_pages'] = [
      '#title' => $this->t('Overview pages'),
      '#type' => 'details',
      '#open' => TRUE,
      '#weight' => '2',
      '#description' => $this->t('These references are used by the system when creating links automatically. Only change these if you create a new overview page.'),
    ];

    $node_reference = Node::load($config->get('initiative_overview'));
    $form['overview_pages']['initiative_overview'] = [
      '#title' => $this->t('Initiative overview page'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#default_value' => $node_reference,
      '#description' => $this->t('The page used for an overview of initiatives'),
      '#weight' => '1',
    ];

    $node_reference = Node::load($config->get('hearings_overview'));
    $form['overview_pages']['hearings_overview'] = [
      '#title' => $this->t('Hearings overview page'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#default_value' => $node_reference,
      '#description' => $this->t('The page used for an overview of hearings'),
      '#weight' => '2',
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
    drupal_set_message('Settings saved');

    // Set the rest of the configuration values.
    $this->getBaseConfig()->setMultiple([
      'footer_text' => $form_state->getValue('footer_text')['value'],
      'hearings_overview' => $form_state->getValue('hearings_overview'),
      'initiative_overview' => $form_state->getValue('initiative_overview'),
    ]);

    drupal_flush_all_caches();
  }

}
