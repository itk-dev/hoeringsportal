<?php

namespace Drupal\hoeringsportal_deskpro\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form definition for applying configuration for Deskpro module in UI.
 */
class DeskproSettingsForm extends FormBase {
  /**
   * The Deskpro service.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\DeskproService
   */
  private $deskpro;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_deskpro.deskpro')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(DeskproService $deskpro) {
    $this->deskpro = $deskpro;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'deskpro_settings_form';
  }

  /**
   * Get key/value storage for base config.
   *
   * @return \Drupal\hoeringsportal_deskpro\State\DeskproConfig
   *   The config object.
   */
  private function getFormConfig() {
    return \Drupal::getContainer()->get('hoeringsportal_deskpro.config');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->getFormConfig();

    $form['deskpro_settings'] = [
      '#type' => 'vertical_tabs',
      '#default_tab' => 'edit-add-hearing-ticket-form',
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
      '#required' => TRUE,
    ];

    $tokens = [
      '[ticket:ref]',
      '[ticket:url]',
    ];

    $form['add_hearing_ticket_form']['ticket_created_confirmation'] = [
      '#title' => $this->t('Confirmation after ticket submit'),
      '#type' => 'text_format',
      '#format' => 'filtered_html',
      '#default_value' => $config->get('ticket_created_confirmation'),
      '#weight' => '2',
      '#size' => 60,
      '#description' => $this->t('Confirmation text shown after ticket is submitted.')
      . '<br/>' . $this->t('Available tokens: @tokens', ['@tokens' => implode(' ', $tokens)]),
    ];

    $values = $config->get('representations');
    $form['add_hearing_ticket_form']['representations'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Representation list'),
      '#weight' => '3',
      '#required' => TRUE,
    ];

    try {
      foreach ($this->deskpro->getRepresentations() as $representation) {
        $id = $representation['id'];
        $defaultValues = $values[$id] ?? [];
        $form['add_hearing_ticket_form']['representations'][$id] = [
          '#type' => 'group',
          'title' => [
            '#type' => 'textfield',
            '#title' => $this->t('Representation title (@title)', ['@title' => $representation['title']]),
            '#default_value' => $defaultValues['title'] ?? $representation['title'],
            '#required' => TRUE,
          ],
          'is_available' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Is available'),
            '#default_value' => $defaultValues['is_available'] ?? FALSE,
          ],
          'require_organization' => [
            '#type' => 'checkbox',
            '#title' => $this->t('Require organization'),
            '#default_value' => $defaultValues['require_organization'] ?? FALSE,
          ],
        ];
      }
    }
    catch (\Throwable $throwable) {
      $this->messenger()->addError('Cannot get representations data from Deskpro.');
    }

    $form['deskpro_integration'] = [
      '#type' => 'details',
      '#title' => $this->t('Deskpro integration'),
      '#open' => TRUE,
      '#group' => 'deskpro_settings',
    ];

    $form['deskpro_integration']['deskpro_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Deskpro url'),
      '#required' => TRUE,
      '#default_value' => $config->get('deskpro_url'),
    ];

    $form['deskpro_integration']['deskpro_api_code_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Deskpro api code key'),
      '#required' => TRUE,
      '#default_value' => $config->get('deskpro_api_code_key'),
    ];

    $form['deskpro_integration']['deskpro_ticket_custom_fields'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#title' => $this->t('Deskpro ticket custom fields'),
    ];

    $customFields = $config->getTicketCustomFields();
    $titles = $config->getTicketCustomFieldTitles();
    foreach ($customFields as $name => $value) {
      $form['deskpro_integration']['deskpro_ticket_custom_fields'][$name] = [
        '#type' => 'number',
        '#title' => $titles[$name] ?? $name,
        '#required' => TRUE,
        '#default_value' => $value,
      ];
    }

    $options = [];
    try {
      $departments = $this->deskpro->getTicketDepartments([], TRUE);
      foreach ($departments as $department) {
        $options[$department['id']] = $department['title'];
      }
    }
    catch (\Throwable $throwable) {
      $this->messenger()->addError('Cannot get departments data from Deskpro.');
    }

    $form['deskpro_integration']['deskpro_available_department_ids'] = [
      '#type' => 'checkboxes',
      '#options' => $options,
      '#title' => $this->t('Available departments'),
      '#required' => count($options) > 0,
      '#default_value' => $config->get('deskpro_available_department_ids', []),
      '#description' => (count($options) > 0) ? $this->t('The departments that can be attached to hearings.') : $this->t('Please enter a valid Deskpro url and api code key and save the form.'),
    ];

    $form['deskpro_integration']['deskpro_data_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Deskpro data token'),
      '#required' => TRUE,
      '#default_value' => $config->get('deskpro_data_token'),
      '#description' => $this->t('Token used for accessing the data api.'),
    ];

    $form['deskpro_integration']['deskpro_cache_ttl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cache ttl'),
      '#required' => TRUE,
      '#default_value' => $config->get('deskpro_cache_ttl', 600),
      '#description' => $this->t('Cache ttl in seconds.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    if (!UrlHelper::isValid($form_state->getValue('deskpro_url'), TRUE)) {
      $form_state->setErrorByName('deskpro_url', $this->t('Please enter a valid url.'));
    }

    // Validate that custom field ids are unique.
    $values = $form_state->getValue('deskpro_ticket_custom_fields');
    $usedIds = [];
    foreach ($values as $name => $id) {
      if (isset($usedIds[$id])) {
        $form_state->setErrorByName('deskpro_ticket_custom_fields][' . $name, $this->t('The id @id is already used by @name.', ['@id' => $id, '@name' => $usedIds[$id]]));
      }
      $usedIds[$id] = $name;
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

      'deskpro_url' => $form_state->getValue('deskpro_url'),
      'deskpro_api_code_key' => $form_state->getValue('deskpro_api_code_key'),
      'deskpro_ticket_custom_fields' => $form_state->getValue('deskpro_ticket_custom_fields'),
      'deskpro_available_department_ids' => array_keys(array_filter($form_state->getValue('deskpro_available_department_ids'))),
      'deskpro_data_token' => $form_state->getValue('deskpro_data_token'),
      'deskpro_cache_ttl' => $form_state->getValue('deskpro_cache_ttl'),
    ]);

    drupal_flush_all_caches();

    $this->messenger()->addMessage($this->t('Your settings have been saved.'));
  }

}
