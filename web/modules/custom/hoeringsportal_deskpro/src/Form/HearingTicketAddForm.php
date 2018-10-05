<?php

namespace Drupal\hoeringsportal_deskpro\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper;
use Drupal\hoeringsportal_deskpro\State\AddHearingTicketFormConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form form definition for adding a hearing.
 */
class HearingTicketAddForm extends FormBase {
  /**
   * The form config.
   *
   * @var \Drupal\hoeringsportal_deskpro\State\AddHearingTicketFormConfig
   */
  private $config;

  /**
   * The hearing helper.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\HearingHelper
   */
  private $helper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_deskpro.form_config'),
      $container->get('hoeringsportal_deskpro.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    AddHearingTicketFormConfig $config,
    HearingHelper $helper
  ) {
    $this->config = $config;
    $this->helper = $helper;
  }

  /**
   * A HACK!
   *
   * @see https://www.drupal.org/project/drupal/issues/2742859
   */
  private function initialize() {
    $container = \Drupal::getContainer();
    if (NULL === $this->config) {
      $this->config = $container->get('hoeringsportal_deskpro.form_config');
    }
    if (NULL === $this->helper) {
      $this->helper = $container->get('hoeringsportal_deskpro.helper');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hearing_ticket_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->initialize();

    $file_validators = [
      'file_validate_size' => [10490000],
    ];

    $form['hearing_intro_text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->config->get('intro'),
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('E-mail'),
      '#required' => TRUE,
    ];

    $form['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address'),
      '#states' => [
        'visible' => [
          ':input[name="address_secret"]' => ['checked' => FALSE],
        ],
        'required' => [
          ':input[name="address_secret"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['geolocation'] = [
      '#type' => 'hidden',
    ];

    $form['address_secret'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('My address is secret'),
    ];

    $representations = $this->config->getRepresentations();
    $options = array_map(function ($item) {
      return $item['label'];
    }, $representations);

    $form['representation'] = [
      '#type' => 'select',
      '#title' => $this->t('I represent'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $representationThatRequireOrganization = $this->config->getRepresentationsThatRequireOrganization();
    $condition = array_map(function ($id) {
      return ['value' => $id];
    }, array_keys($representationThatRequireOrganization));

    $form['organization'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Organization'),
      '#states' => [
        'visible' => [
          ':input[name="representation"]' => $condition,
        ],
        'required' => [
          ':input[name="representation"]' => $condition,
        ],
      ],
    ];

    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#required' => TRUE,
    ];

    $form['message'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Message'),
      '#format' => 'filtered_html',
      '#required' => TRUE,
    ];

    $form['files'] = [
      '#title' => $this->t('Select a file'),
      '#type' => 'managed_file',
      '#upload_validators' => $file_validators,
      '#description' => [
        '#theme' => 'file_upload_help',
        '#upload_validators' => $file_validators,
      ],
      '#multiple' => TRUE,
    ];

    $form['hearing_consent_text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->config->get('consent'),
    ];

    $form['consent'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I have red the terms and give my consent'),
      '#required' => TRUE,
    ];

    $form['spinner'] = [
      '#markup' => '<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#button_type' => 'primary',
    ];

    $form['#attached']['library'][] = 'hoeringsportal_deskpro/form_ticket_add';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->initialize();

    parent::validateForm($form, $form_state);
    $representationThatRequireOrganization = $this->config->getRepresentationsThatRequireOrganization();
    $representation = $form_state->getValue('representation');
    $organization = trim($form_state->getValue('organization'));

    if (isset($representationThatRequireOrganization[$representation])
      && empty($organization)) {
      $form_state->setErrorByName('organization', $this->t('Please enter your organization.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->initialize();

    $names = [
      'name',
      'email',
      'address',
      'geolocation',
      'representation',
      'organization',
      'subject',
      'message',
    ];

    $data = array_map(function ($key) use ($form_state) {
      $value = $form_state->getValue($key);
      if (isset($value['value'], $value['format'])) {
        $value = $value['value'];
      }
      return $value;
    }, array_combine($names, $names));

    if ($form_state->getValue('address_secret')) {
      unset($data['address'], $data['geolocation']);
    }

    // File ids.
    $files = $form_state->getValue('files', []);

    try {
      $node = $this->getRouteMatch()->getParameter('node');
      [$ticket, $message] = $this->helper->createHearingTicket($node, $data, $files);
      $this->messenger()->addMessage($this->t('Your hearing ticket has being submitted and will appear here in a few minutes.'));

      $form_state->setRedirect('entity.node.canonical', ['node' => $node->id()]);
    }
    catch (\Exception $exception) {
      $this->messenger()->addError($this->t('Error submitting hearing ticket'));
      return;
    }

  }

}
