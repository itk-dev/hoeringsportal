<?php

namespace Drupal\hoeringsportal_openid_connect;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\hoeringsportal_openid_connect\Plugin\OpenIDConnectClient\OpenIDConnectCitizenClient;
use Drupal\openid_connect\Entity\OpenIDConnectClientEntity;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * A helper.
 */
class Helper {
  private const SESSION_USER_DATA = 'hoeringsportal_openid_connect_user_data';

  /**
    * The OpenID Connect client storage.
    *
    * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
    */
  readonly private ConfigEntityStorageInterface $clientStorage;

  /**
   * Constructor.
   */
  public function __construct(
    readonly private EntityTypeManagerInterface $entityTypeManager,
    readonly private SessionInterface $session
  ) {
    $this->clientStorage = $this->entityTypeManager->getStorage('openid_connect_client');
  }

  /**
   * Implements hook_openid_connect_pre_authorize()
   */
  public function openidConnectPreAuthorize(UserInterface|bool $account, array $context) {
    $client = $this->loadClient($context['plugin_id']);
    if ($client instanceof OpenIDConnectClientEntity) {
      $plugin = $client->getPlugin();
      if ($plugin instanceof OpenIDConnectCitizenClient) {
        $this->setUserData($context['user_data'], $client);
      }
    }

    // Make sure that no user is actually authorized.
    return FALSE;
  }

  /**
   * Load OpenID Connect client by id.
   */
  public function loadClient(string $clientId): ?OpenIDConnectClientEntity {
    return $this->clientStorage->load($clientId);
  }

  /**
   * Get currect user data.
   */
  public function getUserData(string $pluginId): ?array {
    $data = $this->session->get(self::SESSION_USER_DATA);

    return $data[$pluginId] ?? NULL;
  }

  /**
   * Remove user data.
   */
  public function removeUserData(string $pluginId = NULL): void {
    if (NULL === $pluginId) {
      $this->session->remove(self::SESSION_USER_DATA);
    }
    else {
      $data = $this->session->get(self::SESSION_USER_DATA);
      unset($data[$pluginId]);

      $this->session->set(self::SESSION_USER_DATA, $data);
    }
  }

  /**
   * Set user data.
   */
  private function setUserData(array $userData, OpenIDConnectClientEntity $client): array {
    $data = $this->session->get(self::SESSION_USER_DATA);
    if (!is_array($data)) {
      $data = [];
    }
    $data[$client->id()] = $context['user_data'];
    $this->session->set(self::SESSION_USER_DATA, $data);
  }

}
