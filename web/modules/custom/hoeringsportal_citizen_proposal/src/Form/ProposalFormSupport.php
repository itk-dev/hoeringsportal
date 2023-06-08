<?php

namespace Drupal\hoeringsportal_citizen_proposal\Form;

use Drupal\Core\State\State;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for supporting proposal.
 */
final class ProposalFormSupport extends FormBase {

  /**
   * Constructor for the proposal approve form.
   */
  public function __construct(
    readonly private Helper $helper,
    readonly private State $state,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(Helper::class),
      $container->get('state'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'proposal_support_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = NULL): RedirectResponse|array {
    $adminFormStateValues = $this->state->get('citizen_proposal_admin_form_values');

    // Pass the node to the submit handler.
    $form['#node'] = $node;

    $form['support_intro'] = [
      '#type' => 'processed_text',
      '#format' => $adminFormStateValues['author_intro']['format'] ?? 'filtered_html',
      '#text' => $adminFormStateValues['author_intro']['value'] ?? '',
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this
        ->t('Name'),
      '#default_value' => 'GET THIS FROM SESSION',
      '#attributes' => ['readonly' => TRUE],
      '#description' => $adminFormStateValues['support_name_help'] ?? '',
      '#description_display' => 'before',
    ];

    $form['email'] = [
      '#type' => 'email',
      '#required' => TRUE,
      '#title' => $this
        ->t('Email'),
      '#description' => $adminFormStateValues['support_email_help'] ?? '',
      '#description_display' => 'before',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Support proposal'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->helper->saveSupport([
      'node_id' => $form['#node']->nid->value,
      'user_uuid' => 'UUID FROM SESSION',
      'user_name' => $form_state->getValue('name'),
      'user_email' => $form_state->getValue('email'),
      'created' => time(),

    ]);

    $form_state
      ->setRedirect('entity.node.canonical', ['node' => $form['#node']->nid->value]);
  }

}
