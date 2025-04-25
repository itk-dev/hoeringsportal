<?php

namespace Drupal\hoeringsportal_audit_log\EventSubscriber;

use Drupal\os2web_audit\Service\Logger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
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
   * Constructor.
   */
  public function __construct(
    protected RouteMatchInterface $routeMatch,
    protected AccountInterface $currentUser,
    #[Autowire(service: 'os2web_audit.logger')]
    protected Logger $auditLogger,
    protected readonly ConfigHelper $configHelper,
    protected RequestStack $requestStack,
  ) {
  }

  /**
   * Audit on route change, by configured routes.
   *
   * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
   *   The event
   *   to process.
   */
  public function onController(ControllerEvent $event): void {
    $pathInfo = $event->getRequest()->getPathInfo();
    $requestUri = $event->getRequest()->getRequestUri();
    $urlPatterns = $this->configHelper->getUrlPattern();

    if ($urlPatterns) {
      foreach ($urlPatterns as $urlPattern) {
        if (@preg_match($urlPattern, $requestUri)) {
          $this->logAuditMessage($pathInfo);
          return;
        }
      }
    }

    $currentRouteName = $event->getRequest()->attributes->get('_route');
    $routesThatShouldBeLogged = $this->configHelper->getRouteNames();
    if ($routesThatShouldBeLogged && in_array($currentRouteName, $routesThatShouldBeLogged)) {
      // Early return, if the route is in config no need to do anything besides
      // auditlog.
      $this->logAuditMessage($pathInfo);
      return;
    }

    $parameterBag = $this->routeMatch->getParameters();
    foreach ($parameterBag as $routeParameter) {
      if ($routeParameter instanceof EntityInterface) {
        $entityTypeId = $routeParameter->getEntityTypeId();
        $nodeType = NULL;
        // If it is a node, it has the getType, and we need the nodetype for the
        // config.
        if ($entityTypeId === 'node') {
          /** @var \Drupal\node\Entity\Node $routeParameter */
          $nodeType = $routeParameter->getType();
        }
        if ($this->configHelper->isConfigActive($currentRouteName, $entityTypeId, $nodeType)) {
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
  private function logAuditMessage($info): void {
    $request = $this->requestStack->getCurrentRequest();
    $msg = sprintf(
      '%s request to %s: %s',
      $request?->getMethod(),
      $request?->getPathInfo(),
      $info
    );
    $this->auditLogger->info('Lookup', $msg, logUser: TRUE);
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
