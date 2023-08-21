<?php

namespace Drupal\hoeringsportal_citizen_proposal\Form;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\hoeringsportal_citizen_proposal\Exception\RuntimeException;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;
use Drupal\hoeringsportal_citizen_proposal\Helper\WebformHelper;
use Drupal\hoeringsportal_openid_connect\Controller\OpenIDConnectController;
use Drupal\hoeringsportal_openid_connect\Helper as AuthenticationHelper;
use Drupal\webform\WebformInterface;
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
    readonly protected WebformHelper $webformHelper,
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
      $container->get(WebformHelper::class),
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

    if (!$this->isAuthenticatedAsCitizen() && !$this->isAuthenticatedAsEditor()) {
      $form['authenticate'] = [
        '#type' => 'container',

        'message' => [
          '#type' => 'processed_text',
          '#format' => $this->getAdminFormStateValue(
            ['authenticate_message', 'format'],
            'filtered_html'
          ),
          '#text' => $this->getAuthenticateMessage(),
        ],

        'link' => Link::createFromRoute(
            $this->getAdminFormStateValue('authenticate_link_text', $this->t('Authenticate with MitID')),
              'hoeringsportal_openid_connect.openid_connect_authenticate',
              [
                OpenIDConnectController::QUERY_STRING_DESTINATION => Url::fromRoute('<current>')->toString(TRUE)->getGeneratedUrl(),
              ],
        )->toRenderable()
        + ['#attributes' => ['class' => ['btn', 'btn-primary', 'ml-2']]],
      ];

      return $form;
    }

    if ($this->isAuthenticatedAsCitizen()) {
      $userData = $this->getUserData();
      $form['authenticated'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['authenticate-wrapper', 'py-3']],

        'message' => [
          '#markup' => $this->t("You're currently authenticated as %name", ['%name' => $userData['name']]),
        ],

        'link' => Link::createFromRoute(
          $this->getAdminFormStateValue('end_session_link_text', $this->t('Sign out')),
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
    }

    return $this->buildProposalForm($form, $form_state);
  }

  /**
   * Get message telling user that authentication is needed.
   */
  abstract protected function getAuthenticateMessage(): string|TranslatableMarkup;

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
   *   - phone
   *   - email
   *   - email_display
   *   - title
   *   - proposal
   *   - remarks
   */
  protected function getDefaultFormValues(): array {
    $userData = $this->getUserData();
    $entity = $this->helper->getDraftProposal();

    return [
      'name' => $entity?->field_author_name->value ?? $userData['name'] ?? NULL,
      'phone' => $entity?->field_author_phone->value ?? $userData['phone'] ?? NULL,
      'email' => $entity?->field_author_email->value ?? $userData['email'] ?? NULL,
      'email_display' => $entity?->field_author_email_display->value ?? NULL,
      'title' => $entity?->title->value ?? NULL,
      'proposal' => $entity?->field_proposal->value ?? '',
      'remarks' => $entity?->field_remarks->value ?? '',
    ];
  }

  /**
   * Get admin form value.
   */
  protected function getAdminFormStateValue(string|array $key, string $default = NULL): mixed {
    return $this->helper->getAdminValue($key, $default);
  }

  /**
   * Get admin form value as a URL.
   */
  protected function getAdminFormStateValueUrl(string|array $key, string $default = NULL, Url $defaultUrl = NULL): Url {
    try {
      return Url::fromUserInput($this->helper->getAdminValue($key, $default) ?? '');
    }
    catch (\Exception) {
      return $defaultUrl ?? Url::fromRoute('<front>');
    }
  }

  /**
   * Check if citizen is authenticated.
   */
  protected function isAuthenticatedAsCitizen(): bool {
    try {
      $this->getUserUuid(allowEditor: FALSE);
      return TRUE;
    }
    catch (\Exception) {
      return FALSE;
    }
  }

  /**
   * Check if editor is authenticated.
   */
  protected function isAuthenticatedAsEditor(): bool {
    return $this->currentUser()->isAuthenticated()
      && $this->currentUser()->hasPermission('support citizen proposal on behalf of citizen');
  }

  /**
   * Check if either citizen or editor is authenticated.
   */
  protected function isAuthenticated(): bool {
    return $this->isAuthenticatedAsCitizen() || $this->isAuthenticatedAsEditor();
  }

  /**
   * Get user data.
   */
  protected function getUserData(): ?array {
    return $this->authenticationHelper->getUserData();
  }

  /**
   * De-authenticate (is that a real word?) user.
   */
  protected function deAuthenticateUser(Url $url = NULL): Url {
    if (NULL === $url) {
      $url = Url::fromRoute('<current>');
    }

    if (!$this->isAuthenticatedAsCitizen()) {
      return $url;
    }

    $this->authenticationHelper->removeUserData();
    return Url::fromRoute(
      'hoeringsportal_openid_connect.openid_connect_end_session',
      [
        OpenIDConnectController::QUERY_STRING_DESTINATION => $url->toString(TRUE)->getGeneratedUrl(),
      ],
    );
  }

  /**
   * Get user UUID.
   *
   * @return string
   *   The user UUID.
   */
  protected function getUserUuid($allowEditor = TRUE): string {
    if ($allowEditor && $this->isAuthenticatedAsEditor()) {
      $userId = uniqid('editor', TRUE);
    }
    else {
      $userData = $this->getUserData();
      $userUuidClaim = $this->config->get('user_uuid_claim');
      if (!isset($userData[$userUuidClaim])) {
        throw new RuntimeException('Cannot get user identifier');
      }
      $userId = $userData[$userUuidClaim];
    }

    // Compute a GDPR safe and (hopefully) unique user identifier.
    return sha1($userId);
  }

  /**
   * Load survey webform.
   *
   * @return \Drupal\webform\WebformInterface|null
   *   The webform if any.
   */
  protected function loadSurvey(): ?WebformInterface {
    return $this->webformHelper->loadWebform((string) $this->getAdminFormStateValue([
      'survey',
      'webform',
    ]));
  }

}
