<?php

namespace Drupal\hoeringsportal_openid_connect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\hoeringsportal_openid_connect\Helper;
use Drupal\openid_connect\OpenIDConnectClaims;
use Drupal\openid_connect\OpenIDConnectSessionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * OpenID Connect redirect controller.
 */
final class OpenIDConnectRedirectController extends ControllerBase {

  /**
   * Constructor.
   */
  public function __construct(
    readonly private Helper $helper,
    readonly private OpenIDConnectClaims $claims,
    readonly private OpenIDConnectSessionInterface $session
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
     $container->get(Helper::class),
     $container->get('openid_connect.claims'),
     $container->get('openid_connect.session')
    );
  }

  /**
   * Authorize.
   *
   * Lifted from OpenIDConnectLoginForm::submitForm().
   *
   * @see OpenIDConnectLoginForm::submitForm()
   */
  public function authorize(Request $request, string $client_id) {
    // Make sure that we're sendt back to our authenticate method.
    $this->session->saveTargetLinkUri(
      Url::fromRoute('hoearingsportal_openid_connect.redirect_controller.authenticate', [
        'client_id' => $client_id,
        'destination' => $request->get('destination'),
      ])->toString(TRUE)->getGeneratedUrl(),
    );
    $client = $this->helper->loadClient($client_id);
    $plugin = $client->getPlugin();
    $scopes = $this->claims->getScopes($plugin);
    $this->session->saveOp('login');
    $response = $plugin->authorize($scopes);

    return $response;
  }

  /**
   * Authenticate.
   */
  public function authenticate(Request $request, string $client_id) {
    $destination = $request->get('destination');

    // Delete all messages related to failed login (and everything else).
    $this->messenger()->deleteAll();

    return new TrustedRedirectResponse($destination);
  }

}
