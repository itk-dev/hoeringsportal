<?php

namespace Drupal\hoeringsportal_timeline\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Configuration form for the hoeringsportal timeline module.
 */
class HoeringsportalTimelineConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hoeringsportal_timeline_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['hoeringsportal_timeline.config'];
  }

  /**
   * Get key/value storage for base config.
   *
   * @return object
   *   The settings.
   */
  private function getSettings() {
    return \Drupal::getContainer()->get('hoeringsportal_timeline.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();
    $node_reference = Node::load($settings->get('node_ref'));
    $form['general'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => t('General settings'),
    ];

    $form['general']['node_ref'] = [
      '#title' => $this->t('Timeline page'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#default_value' => $node_reference,
      '#description' => t('Choose which page to add the timeline to'),
      '#weight' => '6',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save to config.
    // Set the configuration values.
    $this->getSettings()->setMultiple([
      'node_ref' => $form_state->getValue('node_ref'),
    ]);

    drupal_flush_all_caches();
  }

}
