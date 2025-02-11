<?php

namespace Drupal\hoeringsportal_audit_log\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\os2web_audit\Service\Logger;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\redirect\RedirectChecker;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * 
 */
final class RoutingListener implements EventSubscriberInterface {
 /**
   * Constructor.
   */
  public function __construct(
    #[Autowire(service: 'os2web_audit.logger')]
    protected Logger $auditLogger
    ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('os2web_audit.logger')
    );
  }

  /**
   * Audit on route change, by configured routes.
   *
   * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
   *   The event to process.
   */
  public function auditOnRouteChange(ControllerEvent $event) {


    $route = $event->getRequest()->getPathInfo();
    $routeName = $event->getRequest()->attributes->get('_route');
    // hoeringsportal_citizen_proposal.admin_supporter
    // Check if the route is for a node view page.
    $user = \Drupal::currentUser()->getEmail();

    if ($routeName === 'entity.node.canonical') {
      $sdf = \Drupal::routeMatch()->getRawParameter("node");
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node = $node_storage->load($sdf);
      var_dump($node->getType());
      $msg = sprintf("Potential fetch personal data from page: %s by %s", $route, $user);
      $this->auditLogger->info('Lookup', $msg);
    } else if ($routeName === "hoeringsportal_citizen_proposal.admin_supporter") {
      $msg = sprintf("Potential fetch personal data from page: %s by %s", $route, $user);
      $this->auditLogger->info('Lookup', $msg);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::CONTROLLER][] = ['auditOnRouteChange', 1000];
    return $events;
  }

}
