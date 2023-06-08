<?php

namespace Drupal\hoeringsportal_openid_connect\Plugin\OpenIDConnectClient;

use Drupal\openid_connect\Plugin\OpenIDConnectClient\OpenIDConnectGenericClient;

/**
 * Citizen OpenID Connect client.
 *
 * Used to authentiate Danish citizens, but not actually log them into Drupal,
 * i.e. no Drupal users are created or really autenticated by the plugin.
 *
 * @OpenIDConnectClient(
 *   id = "hoeringsportal_openid_connect_citizen",
 *   label = @Translation("Citizen")
 * )
 */
class OpenIDConnectCitizenClient extends OpenIDConnectGenericClient {
  public const PLUGIN_ID = 'hoeringsportal_openid_connect_citizen';

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'scopes' => ['openid', 'email', 'profile'],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveUserInfo(string $access_token): ?array {
    $userinfo = parent::retrieveUserInfo($access_token);

    // The OpenID Connect module requires an email on users.
    $userinfo['email'] = uniqid(TRUE) . '@hoeringsportal_openid_connect.example.com';

    return $userinfo;
  }

}
