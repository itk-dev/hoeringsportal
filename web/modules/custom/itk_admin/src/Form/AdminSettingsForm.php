<?php

namespace Drupal\itk_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Admin settings form.
 *
 * @package Drupal\itk_admin\Form
 */
class AdminSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'itk_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $site_name = \Drupal::config('system.site')->get('name');
    // Add intro wrapper.
    $form['intro_wrapper'] = [
      '#title' => $this->t('@site_name settings.', ['@site_name' => $site_name]),
      '#type' => 'item',
      '#description' => $this->t('These pages contain @site_name specific config settings.', ['@site_name' => $site_name]),
      '#weight' => '1',
      '#open' => TRUE,
    ];

    // Get local tasks.
    $manager = \Drupal::service('plugin.manager.menu.local_task');
    $tasks = $manager->getLocalTasks(\Drupal::routeMatch()->getRouteName(), 0);
    unset($tasks['tabs']['itk_admin.admin']);
    $links = '';
    foreach ($tasks['tabs'] as $tab) {
      $link_array = Link::createFromRoute($tab['#link']['title'], $tab['#link']['url']->getRouteName())->toRenderable();
      $links .= '<div>' . \Drupal::service('renderer')->render($link_array) . '</div>';
    }

    // Add menu wrapper.
    $form['menu_wrapper'] = [
      '#title' => $this->t('Configure'),
      '#type' => 'item',
      '#description' => $links,
      '#weight' => '2',
      '#open' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->addMessage('Settings saved');
  }

}
