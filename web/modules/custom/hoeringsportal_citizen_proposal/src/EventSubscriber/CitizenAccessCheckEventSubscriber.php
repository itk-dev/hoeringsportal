<?php

namespace Drupal\hoeringsportal_citizen_proposal\EventSubscriber;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Site\Settings;
use Drupal\hoeringsportal_citizen_proposal\Helper\CitizenAccessChecker;
use Drupal\hoeringsportal_citizen_proposal\Helper\CprHelper;
use Drupal\hoeringsportal_citizen_proposal\Helper\Helper;
use Drupal\hoeringsportal_openid_connect\Event\AccessCheckEvent;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Citizen access check event subscriber.
 */
class CitizenAccessCheckEventSubscriber implements EventSubscriberInterface {
  use LoggerAwareTrait;
  use LoggerTrait;

  /**
   * Constructor.
   */
  public function __construct(
    readonly private ImmutableConfig $config,
    readonly private Helper $helper,
    readonly private CprHelper $cprHelper,
    readonly private CitizenAccessChecker $accessChecker,
    LoggerChannel $logger
  ) {
    $this->setLogger($logger);
  }

  /**
   * Event subscriber action.
   *
   * @param \Drupal\hoeringsportal_openid_connect\Event\AccessCheckEvent $event
   *   The event.
   */
  public function accessCheck(AccessCheckEvent $event) {
    $settings = (array) (Settings::get('hoeringsportal_citizen_proposal')['access_check'] ?? []);

    if (empty($settings)) {
      return;
    }

    $token = $event->getToken();
    $cprClaim = $settings['cpr_user_claim'] ?? NULL;
    $cpr = $token[$cprClaim] ?? NULL;

    if (empty($cpr)) {
      $this->logger->error(sprintf('Cannot get CPR (%s) from user claims', $cprClaim));
      return;
    }

    $accessGranted = FALSE;

    try {
      $result = $this->cprHelper->lookUpCpr($cpr);
      $accessGranted = $this->accessChecker->checkAccess($result);
    }
    catch (\Exception $exception) {
      $this->logger->error('Citizen access check exception: @message', [
        '@message' => $exception->getMessage(),
        '@exception' => $exception,
      ]);
    }

    if (!$accessGranted) {
      $this->logger->debug('Citizen access denied');
      $url = $this->helper->getAdminValue('authenticate_access_denied_page',
        '/access-denied');
      $event->setAccessDeniedLocation($url);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      AccessCheckEvent::class => 'accessCheck',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    $this->logger->log($level, $message, $context);
  }

}
