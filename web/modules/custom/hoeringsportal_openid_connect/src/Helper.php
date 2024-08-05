<?php

namespace Drupal\hoeringsportal_openid_connect;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * A helper.
 */
class Helper {
  private const SESSION_USER_DATA = 'hoeringsportal_openid_connect_user_data';

  /**
   * Constructor.
   */
  public function __construct(
    readonly private SessionInterface $session,
  ) {
  }

  /**
   * Set user data.
   */
  public function setUserData(array $data) {
    $this->session->set(self::SESSION_USER_DATA, $data);
  }

  /**
   * Get current user data for a provider.
   */
  public function getUserData(): ?array {
    $data = $this->session->get(self::SESSION_USER_DATA);

    return $data ? (array) $data : NULL;
  }

  /**
   * Remove user data.
   */
  public function removeUserData(): void {
    $this->session->remove(self::SESSION_USER_DATA);
  }

}
