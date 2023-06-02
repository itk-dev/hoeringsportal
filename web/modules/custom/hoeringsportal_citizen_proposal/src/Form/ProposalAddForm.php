<?php

namespace Drupal\hoeringsportal_citizen_proposal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;

/**
 * Form for adding proposal.
 */
final class ProposalAddForm extends FormBase {

  /**
   * Constructor for the proposal add form.
   */
  public function __construct(
    readonly private Helper $helper,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('Drupal\hoeringsportal_citizen_proposal\Helper\Helper'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proposal_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $entity = $this->helper->tempStoreValid();

    $form['author_intro'] = [
      '#type' => 'processed_text',
      '#text' => '<h2>Registrér dig med CPR-nummer og navn.</h2><p>Hent denne tekst fra config.</p>',
      '#format' => 'filtered_html',
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this
        ->t('Name'),
      '#default_value' => 'GET THIS FROM SESSION',
      '#attributes' => ['readonly' => TRUE],
    ];

    $form['email'] = [
      '#type' => 'email',
      '#required' => TRUE,
      '#title' => $this
        ->t('Email'),
      '#default_value' => $entity?->field_author_email->value ?? '',
    ];

    $form['proposal_intro'] = [
      '#type' => 'processed_text',
      '#text' => '<h2>Skriv forslag.</h2><p>Hent denne tekst fra config.</p>',
      '#format' => 'filtered_html',
    ];

    $form['title'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#rows' => 3,
      '#title' => $this
        ->t('Title'),
      '#description' => '<div>Hent hjælpetekst fra config</div>',
      '#description_display' => 'before',
      '#default_value' => $entity?->title->value ?? '',
    ];

    $form['proposal'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#rows' => 15,
      '#title' => $this
        ->t('Proposal'),
      '#description' => '<div>Hent hjælpetekst fra config</div>',
      '#description_display' => 'before',
      '#default_value' => $entity?->field_proposal->value ?? '',
    ];

    $form['remarks'] = [
      '#type' => 'textarea',
      '#required' => TRUE,
      '#rows' => 15,
      '#title' => $this
        ->t('Remarks'),
      '#description' => '<div>Hent hjælpetekst fra config</div>',
      '#description_display' => 'before',
      '#default_value' => $entity?->field_remarks->value ?? '',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $entity ? $this->t('Update proposal') : $this->t('Create proposal'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // @todo add real UUID.
    $entity = Node::create([
      'type' => 'citizen_proposal',
      'title' => $form_state->getValue('title'),
      'field_author_uuid' => 'GET UUID FROM SESSION',
      'field_author_name' => $form_state->getValue('name'),
      'field_author_email' => $form_state->getValue('email'),
      'field_proposal' => [
        'value' => $form_state->getValue('proposal'),
        'format' => 'filtered_html',
      ],
      'field_remarks' => [
        'value' => $form_state->getValue('remarks'),
        'format' => 'filtered_html',
      ],
    ]);
    $this->helper->tempStoreAddEntity($entity);
    $form_state
      ->setRedirect('hoeringsportal_citizen_proposal.citizen_proposal.proposal_approve');
  }

}
