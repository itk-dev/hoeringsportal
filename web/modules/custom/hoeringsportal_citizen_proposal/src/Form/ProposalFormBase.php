<?php

namespace Drupal\hoeringsportal_citizen_proposal\Form;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\State\State;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;
use Drupal\hoeringsportal_openid_connect\Controller\OpenIDConnectController;
use Drupal\hoeringsportal_openid_connect\Helper as AuthenticationHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
    readonly private AuthenticationHelper $authenticationHelper,
    readonly private ImmutableConfig $config
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(Helper::class),
      $container->get('state'),
      $container->get(AuthenticationHelper::class),
      $container->get('config.factory')->get('hoeringsportal_citizen_proposal.settings')
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

      $form['authenticate'] = [
        '#type' => 'container',

        'message' => [
          '#type' => 'processed_text',
          '#format' => $adminFormStateValues['authenticate_message']['format'] ?? 'filtered_html',
          '#text' => $this->getAuthenticateMessage($adminFormStateValues),
        ],

        'link' => Link::createFromRoute(
            $adminFormStateValues['authenticate_link_text'] ?? $this->t('Authenticate with MitID'),
              'hoeringsportal_openid_connect.openid_connect_authenticate',
              [
                OpenIDConnectController::QUERY_STRING_DESTINATION => Url::fromRoute('<current>')->toString(TRUE)->getGeneratedUrl(),
              ],
        )->toRenderable()
        + ['#attributes' => ['class' => ['btn', 'btn-primary', 'ml-2']]],
      ];

      return $form;
    }

    $form['authenticated'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['authenticate-wrapper', 'py-3']],

      'message' => [
        '#markup' => $this->t("You're currently authenticated as %name", ['%name' => $userData['name']]),
      ],

      'link' => Link::createFromRoute(
          $adminFormStateValues['end_session_link_text'] ?? $this->t('Sign out'),
          'hoeringsportal_openid_connect.openid_connect_end_session',
          [
            OpenIDConnectController::QUERY_STRING_DESTINATION => Url::fromRoute('<current>')->toString(TRUE)->getGeneratedUrl(),
          ],
      )->toRenderable()
      + [
        '#attributes' => [
          'class' => ['btn', 'btn-secondary', 'ml-2', 'btn-sign-out'],
        ],
      ],
    ];

    return $this->buildProposalForm($form, $form_state);
  }

  /**
   * Get message telling user that authentication is needed.
   */
  abstract protected function getAuthenticateMessage(array $adminFormStateValues): string|TranslatableMarkup;

  /**
   * Build proposal form.
   */
  abstract protected function buildProposalForm(array $form, FormStateInterface $formState): array|RedirectResponse;

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
      'email_display' => $entity?->field_author_email_display->value ?? NULL,
      'title' => $entity?->title->value ?? NULL,
      'proposal' => $entity?->field_proposal->value ?? '',
      'remarks' => $entity?->field_remarks->value ?? '',
    ];
  }

  /**
   * Get admin form state values.
   */
  protected function getAdminFormStateValues(): array {
    return $this->state->get(ProposalAdminForm::ADMIN_FORM_VALUES_STATE_KEY) ?: [];
  }

  /**
   * Get user data.
   */
  protected function getUserData(): ?array {
    return $this->authenticationHelper->getUserData();
  }

  /**
   * Get user UUID.
   *
   * @return string
   *   The user UUID.
   */
  protected function getUserUuid(): string {
    $userData = $this->getUserData();
    $userUuidClaim = $this->config->get('user_uuid_claim');
    if (isset($userUuidClaim, $userData[$userUuidClaim])) {
      return $userData[$userUuidClaim];
    }

    // Build a user fingerprint.
    return md5(json_encode([
      'cpr' => $userData['cpr'] ?? NULL,
      'name' => $userData['name'] ?? NULL,
    ]));
  }

}
