<?php

namespace Drupal\hoeringsportal_citizen_proposal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\State\State;
use Drupal\Core\Url;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;
use Drupal\hoeringsportal_openid_connect\Helper as AuthenticationHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form for adding proposal.
 */
abstract class ProposalFormBase extends FormBase {

  /**
   * Constructor for the proposal add form.
   */
  final public function __construct(
    readonly protected Helper $helper,
    readonly private State $state,
    readonly private AuthenticationHelper $authenticationHelper
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(Helper::class),
      $container->get('state'),
      $container->get(AuthenticationHelper::class)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // https://www.drupal.org/forum/support/module-development-and-code-questions/2020-06-01/sessions-and-privatetempstore-for#comment-14016801
    $form['#cache'] = ['max-age' => 0];

    $userData = $this->getUserData();
    if (empty($userData)) {
      $adminFormStateValues = $this->getAdminFormStateValues();
      $form['authenticate_message'] = [
        '#type' => 'processed_text',
        '#format' => $adminFormStateValues['authenticate_message']['format'] ?? 'filtered_html',
        '#text' => $adminFormStateValues['authenticate_message']['value'] ?? '',
      ];

      $form['authenticate_link'] = Link::createFromRoute(
        $adminFormStateValues['authenticate_link_text'] ?? $this->t('Authenticate with MitID'),
        'hoearingsportal_openid_connect.redirect_controller.authorize',
        [
          'client_id' => $this->getClientId(),
          'destination' => Url::fromRoute('<current>')->toString(TRUE)->getGeneratedUrl(),
        ],
      )->toRenderable()
        + ['#attributes' => ['class' => ['btn', 'btn-secondary', 'mb-2']]];

      return $form;
    }

    return $this->buildProposalForm($form, $form_state);
  }

  /**
   * Build proposal form.
   */
  abstract protected function buildProposalForm(array $form, FormStateInterface $formState): array;

  /**
   * Get default form values from any existing draft proposal and any user data.
   *
   * @return array
   *   The form default values with keys
   *   - name
   *   - email
   *   - title
   *   - proposal
   *   - remarks
   */
  protected function getDefaultFormValues(): array {
    $userData = $this->getUserData();
    $entity = $this->helper->getDraftProposal();

    return [
      'name' => $entity?->field_author_name->value ?? $userData['name'] ?? NULL,
      'email' => $entity?->field_author_email->value ?? $userData['email'] ?? NULL,
      'title' => $entity?->title->value ?? NULL,
      'proposal' => $entity?->field_proposal->value ?? '',
      'remarks' => $entity?->field_remarks->value ?? '',
    ];
  }

  /**
   * Get admin form state values.
   */
  protected function getAdminFormStateValues(): ?array {
    return $this->state->get(ProposalAdminForm::ADMIN_FORM_VALUES_STATE_KEY);
  }

  /**
   * Get user data.
   */
  private function getUserData(): ?array {
    return $this->authenticationHelper->getUserData($this->getClientId());
  }

  /**
   * Get OpenID Connect client id.
   */
  private function getClientId(): string {
    // @todo Get this from config.
    return 'citizen_authentification';
  }

}
