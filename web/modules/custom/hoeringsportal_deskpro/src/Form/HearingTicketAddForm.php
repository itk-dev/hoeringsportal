<?php

namespace Drupal\hoeringsportal_deskpro\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper;
use Drupal\hoeringsportal_deskpro\State\DeskproConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form form definition for adding a hearing.
 */
class HearingTicketAddForm extends FormBase {
  /**
   * The form config.
   *
   * @var \Drupal\hoeringsportal_deskpro\State\DeskproConfig
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
      $container->get('hoeringsportal_deskpro.config'),
      $container->get('hoeringsportal_deskpro.helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    DeskproConfig $config,
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
      $this->config = $container->get('hoeringsportal_deskpro.config');
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
      '#title' => $this->t('Email address'),
      '#required' => TRUE,
    ];

    $form['email_confirm'] = [
      '#type' => 'email',
      '#title' => $this->t('Confirm email address'),
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
    $stateCondition = [];
    $options = [];
    foreach ($representations as $id => $representation) {
      $options[$id] = $representation['title'];
      if ($representation['require_organization']) {
        $stateCondition[] = ['value' => $id];
      }
    }

    $form['representation'] = [
      '#type' => 'select',
      '#title' => $this->t('I represent'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $states = [
      'visible' => [
        ':input[name="representation"]' => $stateCondition,
      ],
      'required' => [
        ':input[name="representation"]' => $stateCondition,
      ],
    ];

    $form['organization'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Organization'),
      '#states' => $states,
    ];

    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#required' => TRUE,
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
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

    $form['name']['#default_value'] = uniqid('u');
    $form['email']['#default_value'] = uniqid('u') . '@example.com';
    $form['email_confirm']['#default_value'] = $form['email']['#default_value'];
    $form['address']['#default_value'] = 'Dokk1';
    $form['geolocation']['#default_value'] = '';
    $form['address_secret']['#default_value'] = FALSE;
    $form['representation']['#default_value'] = 36;
    $form['organization']['#default_value'] = '';
    $form['subject']['#default_value'] = 'Test ' . date('c');
    $form['message']['#default_value'] = 'Test ' . date('c');
    $form['consent']['#default_value'] = TRUE;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->initialize();

    parent::validateForm($form, $form_state);

    if ($form_state->getValue('email_confirm') !== $form_state->getValue('email')) {
      $form_state->setErrorByName('email_confirm', $this->t('Confirmation email does not match email.'));
    }

    $representation = $form_state->getValue('representation');
    $organization = trim($form_state->getValue('organization'));
    $representations = $this->config->getRepresentations();

    if ($representations[$representation]['require_organization'] && empty($organization)) {
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
      'address_secret',
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

      // @TODO: Show confirmation page.
      $ticket['url'] = $this->helper->getTicketUrl($ticket);
      $message = $this->helper->replaceTokens($this->config->get('ticket_created_confirmation'), [
        'ticket' => $ticket,
      ]);
      $this->messenger()->addMessage($message);

      $form_state->setRedirect('entity.node.canonical', ['node' => $node->id()]);
    }
    catch (\Exception $exception) {
      $this->messenger()->addError($this->t('Error submitting hearing ticket'));
      return;
    }
  }

}
