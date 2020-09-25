<?php

namespace Drupal\hoeringsportal_config_settings\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * TODO: Description of what the class does.
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

    $form['general_settings'] = [
      '#type' => 'vertical_tabs',
      '#default_tab' => 'edit-footer',
    ];

    $form['footer'] = [
      '#title' => $this->t('Footer'),
      '#type' => 'details',
      '#open' => TRUE,
      '#weight' => '0',
      '#group' => 'general_settings',
    ];

    $form['footer']['footer_menu_link'] = [
      '#title' => $this->t('Footer bottom menu'),
      '#type' => 'details',
      '#open' => TRUE,
      '#weight' => '5',
    ];

    $form['footer']['footer_menu_link']['link'] = [
      '#title' => $this
        ->t('Footer bottom links'),
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.menu.edit_form', ['menu' => 'footer']),
    ];

    $form['footer']['footer_text'] = [
      '#title' => $this->t('Footer text first column'),
      '#type' => 'text_format',
      '#format' => 'filtered_html',
      '#default_value' => $config->get('footer_text'),
      '#weight' => '1',
    ];

    $form['footer']['footer_text_2nd'] = [
      '#title' => $this->t('Footer text seccond column'),
      '#type' => 'text_format',
      '#format' => 'filtered_html',
      '#default_value' => $config->get('footer_text_2nd'),
      '#weight' => '1',
    ];

    $form['footer']['footer_text_3rd'] = [
      '#title' => $this->t('Footer text third column'),
      '#type' => 'text_format',
      '#format' => 'filtered_html',
      '#default_value' => $config->get('footer_text_3rd'),
      '#weight' => '1',
    ];

    $form['footer']['footer_text_4th'] = [
      '#title' => $this->t('Footer text forth column'),
      '#type' => 'text_format',
      '#format' => 'filtered_html',
      '#default_value' => $config->get('footer_text_4th'),
      '#weight' => '1',
    ];

    $form['overview_pages'] = [
      '#title' => $this->t('Overview pages'),
      '#type' => 'details',
      '#open' => TRUE,
      '#weight' => '2',
      '#description' => $this->t('These references are used by the system when creating links automatically. Only change these if you create a new overview page.'),
      '#group' => 'general_settings',
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

    $form['remote_paths'] = [
      '#title' => $this->t('External references'),
      '#type' => 'details',
      '#open' => TRUE,
      '#weight' => '2',
      '#description' => $this->t('Links to external pages.'),
      '#group' => 'general_settings',
    ];

    $form['remote_paths']['full_map_project_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Map of all projects'),
      '#size' => 30,
      '#default_value' => $config->get('full_map_project_url'),
      '#description' => $this->t('Used when linking to a map of all projects.'),
    ];

    $form['remote_paths']['full_map_hearing_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Map of all hearings'),
      '#size' => 30,
      '#default_value' => $config->get('full_map_hearing_url'),
      '#description' => $this->t('Used when linking to a map of all hearings.'),
    ];

    $form['misc'] = [
      '#title' => $this->t('Pages'),
      '#type' => 'details',
      '#open' => TRUE,
      '#weight' => '2',
      '#group' => 'general_settings',
    ];

    $node_reference = Node::load($config->get('frontpage_id'));
    $form['misc']['frontpage_id'] = [
      '#title' => $this->t('Front page'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#default_value' => $node_reference,
      '#required' => TRUE,
    ];

    $form['misc']['users_manual_url'] = [
      '#title' => $this->t("User's manual url"),
      '#type' => 'url',
      '#default_value' => $config->get('users_manual_url'),
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
      'footer_text_2nd' => $form_state->getValue('footer_text_2nd')['value'],
      'footer_text_3rd' => $form_state->getValue('footer_text_3rd')['value'],
      'footer_text_4th' => $form_state->getValue('footer_text_4th')['value'],
      'hearings_overview' => $form_state->getValue('hearings_overview'),
      'initiative_overview' => $form_state->getValue('initiative_overview'),
      'full_map_project_url' => $form_state->getValue('full_map_project_url'),
      'full_map_hearing_url' => $form_state->getValue('full_map_hearing_url'),
      'frontpage_id' => $form_state->getValue('frontpage_id'),
      'users_manual_url' => $form_state->getValue('users_manual_url'),
    ]);

    drupal_flush_all_caches();
  }

}
