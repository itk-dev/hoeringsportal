<?php

namespace Drupal\hoeringsportal_citizen_proposal\Form;

use Drupal\Core\State\State;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for approving proposal.
 */
final class ProposalApproveForm extends FormBase {

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
    return 'proposal_approve_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): RedirectResponse|array {
    // https://www.drupal.org/forum/support/module-development-and-code-questions/2020-06-01/sessions-and-privatetempstore-for#comment-14016801
    $form['#cache'] = ['max-age' => 0];

    $adminFormStateValues = $this->state->get('citizen_proposal_admin_form_values');

    if (!$entity = $this->helper->getDraftProposal()) {
      return $this->helper->abandonSubmission();
    }

    $form['approve_form_help'] = [
      '#type' => 'processed_text',
      '#format' => $adminFormStateValues['approve_intro']['format'] ?? 'filtered_html',
      '#text' => $adminFormStateValues['approve_intro']['value'] ?? '',
    ];

    $form['approve_form_title'] = [
      '#prefix' => '<h5>' . $this->t('Title') . '</h5>',
      '#type' => 'processed_text',
      '#text' => $entity?->title->value ?? '',
      '#format' => 'filtered_html',
    ];

    $form['approve_form_proposal'] = [
      '#prefix' => '<h5>' . $this->t('Proposal') . '</h5>',
      '#type' => 'processed_text',
      '#text' => $entity?->field_proposal->value ?? '',
      '#format' => 'filtered_html',
    ];

    $form['approve_form_remarks'] = [
      '#prefix' => '<h5>' . $this->t('Remarks') . '</h5>',
      '#type' => 'processed_text',
      '#text' => $entity?->field_remarks->value ?? '',
      '#format' => 'filtered_html',
    ];

    $form['approve_form_name'] = [
      '#prefix' => '<h5>' . $this->t('Name') . '</h5>',
      '#type' => 'processed_text',
      '#text' => $entity?->field_author_name->value ?? '',
      '#format' => 'filtered_html',
    ];

    $form['approve_form_email'] = [
      '#prefix' => '<h5>' . $this->t('E-mail') . '</h5>',
      '#type' => 'processed_text',
      '#text' => $entity?->field_author_email->value ?? '',
      '#format' => 'filtered_html',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Approve proposal'),
      '#button_type' => 'primary',
    ];

    $form['actions']['edit_link'] = [
      '#title' => $this->t('Edit proposal'),
      '#type' => 'link',
      '#url' => Url::fromRoute('hoeringsportal_citizen_proposal.citizen_proposal.proposal_add'),
      '#attributes' => ['class' => ['btn', 'btn-secondary', 'mb-2']],
    ];

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel proposal'),
      '#button_type' => 'link',
      '#submit' => [[$this, 'cancelSubmit']],
      '#attributes' => ['class' => ['btn', 'btn-link']],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!$entity = $this->helper->getDraftProposal()) {
      return $this->helper->abandonSubmission();
    }

    $this->messenger()->addStatus($this->t('Thank you for you submission.'));
    $entity->save();
    $this->helper->deleteDraftProposal();
    $form_state
      ->setRedirect('<front>');
  }

  /**
   * Custom submit handler for cancelling a submission.
   */
  public function cancelSubmit(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('Your submission has been cancelled.'));
    $this->helper->deleteDraftProposal();
    $form_state
      ->setRedirect('<front>');
  }

}
