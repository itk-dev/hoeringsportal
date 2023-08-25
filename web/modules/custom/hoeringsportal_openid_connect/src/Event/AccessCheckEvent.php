<?php

namespace Drupal\hoeringsportal_openid_connect\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Access check event.
 */
class AccessCheckEvent extends Event {

  /**
   * Access denied.
   *
   * @var bool
   */
  private bool $accessDenied = FALSE;

  /**
   * Access denied location.
   *
   * @var string|null
   */
  private ?string $accessDeniedLocation = NULL;

  /**
   * Constructor.
   */
  public function __construct(
    readonly private array $token,
    readonly private string $loginLocation
  ) {}

  /**
   * Get token.
   */
  public function getToken(): array {
    return $this->token;
  }

  /**
   * Get login location.
   */
  public function getLoginLocation(): string {
    return $this->loginLocation;
  }

  /**
   * Get access denied.
   */
  public function getAccessDenied(): bool {
    return $this->accessDenied;
  }

  /**
   * Set access denied.
   */
  public function setAccessDenied(bool $accessDenied = TRUE): self {
    $this->accessDenied = TRUE;

    return $this;
  }

  /**
   * Get access denied location.
   */
  public function getAccessDeniedLocation(): ?string {
    return $this->accessDeniedLocation;
  }

  /**
   * Set access denied location.
   */
  public function setAccessDeniedLocation(string $accessDeniedLocation) {
    $this->accessDeniedLocation = $accessDeniedLocation;

    return $this->setAccessDenied();
  }

}
