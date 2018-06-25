<?php
/**
 * @file
 * Configuration form for the hoeringsportal hotness module.
 */

namespace Drupal\hoeringsportal_hotness\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Configuration form for the hoeringsportal hotness module.
 */
class HoeringsportalHotnessConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hoeringsportal_hotness_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['hoeringsportal_hotness.config'];
  }

  /**
   * Get key/value storage for base config.
   *
   * @return object
   */
  private function getSettings() {
    return \Drupal::getContainer()->get('hoeringsportal_hotness.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();

    $form['general'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('General settings'),
      '#description' => t('Set the number of flags on nodes for them to change temperature.'),
    ];

    $form['general']['hotness1'] = [
      '#type' => 'number',
      '#title' => t('Cold (1)'),
      '#default_value' => 0,
      '#attributes' => array(
        'disabled' => 'disabled'
      ),
    ];

    $form['general']['hotness2'] = [
      '#type' => 'number',
      '#title' => t('2'),
      '#required' => TRUE,
      '#default_value' => $settings->get('2'),
    ];

    $form['general']['hotness3'] = [
      '#type' => 'number',
      '#title' => t('3'),
      '#required' => TRUE,
      '#default_value' => $settings->get('3'),
    ];

    $form['general']['hotness4'] = [
      '#type' => 'number',
      '#title' => t('4'),
      '#required' => TRUE,
      '#default_value' => $settings->get('4'),
    ];

    $form['general']['hotness5'] = [
      '#type' => 'number',
      '#title' => t('Hot (5)'),
      '#required' => TRUE,
      '#default_value' => $settings->get('5'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save to config.
    // Set the configuration values.
    $this->getSettings()->setMultiple(array(
      '2' => $form_state->getValue('hotness2'),
      '3' => $form_state->getValue('hotness3'),
      '4' => $form_state->getValue('hotness4'),
      '5' => $form_state->getValue('hotness5'),
    ));

    $indicators = \Drupal::getContainer()->get('hoeringsportal_hotness.settings')->getAll();
    $db = \Drupal::database();

    $result = $db->select('flag_counts', 'f')
      ->fields('f')
      ->condition('flag_id', 'promote', '=')
      ->execute()
      ->fetchAllAssoc('entity_id');

    foreach ($result as $value) {
      if (isset($indicators)) {
        $modified_value = 1;
        foreach ($indicators as $key => $indicator) {
          if ($value->count >= $indicator) {
            $modified_value = $key;
          }
        }
        $node = Node::load($value->entity_id);
        $node->field_hotness->value = $modified_value;
        $node->save();
      }
    }

    drupal_flush_all_caches();
  }
}
