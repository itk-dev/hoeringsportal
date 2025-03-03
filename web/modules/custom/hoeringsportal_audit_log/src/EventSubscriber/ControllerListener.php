<?php

namespace Drupal\hoeringsportal_audit_log\EventSubscriber;

use Drupal\os2web_audit\Service\Logger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\hoeringsportal_audit_log\Form\SettingsForm;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\hoeringsportal_audit_log\Helpers\ConfigHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityInterface;

/**
 * Controller listener.
 */
final class ControllerListener implements EventSubscriberInterface {


  /**
   * The module config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $moduleConfig;

  /**
   * Constructor.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    protected RouteMatchInterface $routeMatch,
    protected AccountInterface $currentUser,
    #[Autowire(service: 'os2web_audit.logger')]
    protected Logger $auditLogger,
    protected readonly ConfigHelper $configHelper,
    protected RequestStack $requestStack,
  ) {
    $this->moduleConfig = $configFactory->get(SettingsForm::SETTINGS);
  }

  /**
   * Audit on route change, by configured routes.
   *
   * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
   *   The event to process.
   */
  public function onController(ControllerEvent $event) {
    $pathInfo = $event->getRequest()->getPathInfo();
    $routeName = $event->getRequest()->attributes->get('_route');
    $loggedRouteNames = $this->moduleConfig->get('logged_route_names');

    if ($loggedRouteNames && in_array($routeName, $loggedRouteNames)) {
      $this->logAuditMessage($pathInfo);
      return;
    }

    $routeParameters = $this->routeMatch->getParameters();

    foreach ($routeParameters as $routeParamter) {
      if ($routeParamter instanceof EntityInterface) {
        $entityTypeId = $routeParamter->getEntityTypeId();
        $type = NULL;
        if (method_exists($routeParamter, 'getType')) {
          $type = $routeParamter->getType();
        }
        if ($this->configHelper->getEntityConfiguration($entityTypeId, $routeName, $type)) {
          $this->logAuditMessage($pathInfo);
          return;
        }
      }
    }
  }

  /**
   * Log the path and user email.
   *
   * @param string $info
   *   The path info to include in the message.
   */
  private function logAuditMessage($info) {
    $accountName = $this->currentUser->getAccountName();
    $request = $this->requestStack->getCurrentRequest();
    $msg = sprintf(
      '%s made a %s request to %s: %s',
      $accountName,
      $request?->getMethod(),
      $request?->getPathInfo(),
      $info
    );
    $this->auditLogger->info('Lookup', $msg);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::CONTROLLER => [['onController', 1000]],
    ];
  }

}
