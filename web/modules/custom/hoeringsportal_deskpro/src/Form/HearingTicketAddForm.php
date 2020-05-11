<?php

namespace Drupal\hoeringsportal_deskpro\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper;
use Drupal\hoeringsportal_deskpro\State\DeskproConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Nicoeg\Dawa\Dawa;

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
      'file_validate_extensions' => ['jpg jpeg gif png txt doc docx xls xlsx pdf ppt pptx pps odt ods odp'],
    ];

    $form['hearing_intro_text'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->config->get('intro'),
    ];

    $representations = $this->config->getRepresentations();
    $stateCondition = [];
    $options = [];
    foreach ($representations as $id => $representation) {
      $options[$id] = $representation['title'];
      if ($representation['require_organization']) {
        $stateCondition[] = ['value' => (string) $id];
      }
    }

    $form['representation'] = [
      '#attributes' => ['class' => ['group-representation']],
      '#description' => $this->t('Specify whether you give your personal opinion or represent an organization.'),
      '#description_position' => 'top',
    ];

    $form['contact'] = [
      '#type' => 'container',
      '#title' => $this->t('Contact information'),
      '#attributes' => ['class' => ['group-contact-information']],
      '#description_position' => 'top',

      '#states' => [
        'invisible' => [
          ':input[name="representation"]' => ['value' => ''],
        ],
      ],
    ];

    $form['reply'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Hearing reply'),
      '#attributes' => ['class' => ['group-hearing-reply']],
      '#description_position' => 'top',

      '#states' => [
        'invisible' => [
          ':input[name="representation"]' => ['value' => ''],
        ],
      ],
    ];

    $form['representation']['representation'] = [
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

    $form['contact']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your name'),
      '#required' => TRUE,
    ];

    $form['contact']['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email address'),
      '#required' => TRUE,
    ];

    $form['contact']['email_confirm'] = [
      '#type' => 'email',
      '#title' => $this->t('Confirm email address'),
      '#required' => TRUE,
    ];

    $form['contact']['address'] = [
      '#type' => 'container',
      '#description' => $this->t('Your address will not be shown on the website.'),
      '#description_position' => 'top',

      '#states' => [
        'visible' => [
          ':input[name="address_secret"]' => ['checked' => FALSE],
        ],
        'required' => [
          ':input[name="address_secret"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['contact']['address']['postal_code_and_city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Postal code and city'),
      '#attributes' => ['autocomplete' => 'off'],
      '#required' => TRUE,
    ];

    $form['contact']['address']['street_and_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Street and number'),
      '#attributes' => ['autocomplete' => 'off'],
      '#required' => TRUE,
    ];

    $form['contact']['postal_code'] = [
      '#type' => 'hidden',
    ];

    $form['contact']['address_secret'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('My address is secret'),

      '#states' => [
        'invisible' => [
          ':input[name="representation"]' => $stateCondition,
        ],
      ],
    ];

    $form['reply']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#required' => TRUE,
    ];

    $form['reply']['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#required' => TRUE,
    ];

    $form['reply']['files'] = [
      '#title' => $this->t('Select a file'),
      '#type' => 'managed_file',
      '#upload_validators' => $file_validators,
      '#description' => [
        '#theme' => 'file_upload_help',
        '#upload_validators' => $file_validators,
      ],
      '#multiple' => TRUE,
    ];

    $form['reply']['hearing_consent_text'] = [
      '#markup' => $this->config->get('consent'),
    ];

    $form['reply']['consent'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I have red the terms and give my consent'),
      '#required' => TRUE,
    ];

    $form['reply']['spinner'] = [
      '#markup' => '<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>',
    ];

    $form['reply']['submit'] = [
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

    if ($form_state->getValue('email_confirm') !== $form_state->getValue('email')) {
      $form_state->setErrorByName('email_confirm', $this->t('Confirmation email does not match email.'));
    }

    $representation = $form_state->getValue('representation');
    $organization = trim($form_state->getValue('organization'));
    $representations = $this->config->getRepresentations();

    if ($representations[$representation]['require_organization'] && empty($organization)) {
      // @TODO Customer has to decide if we need an organization name.
      // $form_state->setErrorByName('organization', $this->t('Please enter your organization.'));
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
      'postal_code',
      'postal_code_and_city',
      'street_and_number',
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

    $data['address'] = implode(', ', [$data['street_and_number'], $data['postal_code_and_city']]);
    unset($data['street_and_number'], $data['postal_code_and_city']);

    if ($form_state->getValue('address_secret')) {
      unset($data['address']);
    }

    if (isset($data['address'])) {
      // Try to get geolocation from DAWA.
      try {
        $dawa = new Dawa();
        $result = $dawa->accessAddressSearch($data['address']);
        if (1 === count($result)) {
          $data['geolocation'] = implode(', ', $result[0]->adgangspunkt->koordinater);
        }
      }
      catch (\Exception $e) {
      }
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
