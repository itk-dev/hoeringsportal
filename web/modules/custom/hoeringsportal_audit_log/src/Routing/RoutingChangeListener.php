<?php

namespace Drupal\hoeringsportal_audit_log\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\os2web_audit\Service\Logger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\hoeringsportal_audit_log\Form\SettingsForm;
use Drupal\node\Entity\Node;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Route change listener.
 */
final class RoutingChangeListener implements EventSubscriberInterface {

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
  ) {
    $this->moduleConfig = $configFactory->get(SettingsForm::SETTINGS);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('current_route_match'),
      $container->get('current_user'),
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
    $pathInfo = $event->getRequest()->getPathInfo();
    $routeName = $event->getRequest()->attributes->get('_route');
    $contentTypes = $this->moduleConfig->get('logged_content_types');
    $loggedRouteNames = $this->moduleConfig->get('logged_route_names');

    // Initialize content type arrays for view, edit,
    // and create audit log pages.
    $view = [];
    $edit = [];
    $add = [];

    $editRoute = 'entity.node.edit_form';
    $addRoute = 'entity.node.add.';
    $viewRoute = 'entity.node.canonical';

    // Loop through content types to categorize enabled audit log
    // pages (view, edit, add).
    foreach ($contentTypes as $contentType => $pages) {
      foreach ($pages as $page => $enabled) {
        // Categorize, only if the page is enabled (1 represents a checked
        // checkbox in the form)
        if ($enabled === 1) {
          switch ($page) {
            case 'view':
              $view[] = $contentType;
              break;

            case 'edit':
              $edit[] = $contentType;
              break;

            case 'create':
              $add[] = $contentType;
              break;
          }
        }
      }
    }

    // Early return if there is not assigned audit log to any pages.
    if (empty($view) && empty($edit) && empty($add) && empty($loggedRouteNames)) {
      return;
    }

    // Check if the route name is in the array of hardcoded routes
    // from the settings file.
    if (in_array($routeName, $loggedRouteNames)) {
      $this->logAuditMessage($pathInfo);
      return;
    }

    // Get node to get content type of current page.
    $nodeId = $this->routeMatch->getRawParameter('node');

    if ($nodeId) {
      $node = Node::load($nodeId);
      $routes = [
        [$edit, $editRoute],
        [$add, $addRoute],
        [$view, $viewRoute],
      ];
      foreach ($routes as [$page, $route]) {
        if ($this->auditOnNodePage($routeName, $page, $route, $node)) {
          // If the auditOnNodePage audits, then there is no need to do anymore.
          return;
        }
      }
    }
  }

  /**
   * Log the path and user email.
   *
   * @param string $routeName
   *   The route name.
   * @param array $pageArray
   *   The array of pages where logging is enabled.
   * @param string $key
   *   The key to check if in above array.
   * @param \Drupal\node\Entity\Node $node
   *   For type and title.
   */
  private function auditOnNodePage(string $routeName, array $pageArray, string $key, Node $node): bool {
    // Check if the route corresponds to a node page for auditing.
    if (!empty($pageArray) && strpos($routeName, $key) === 0 && in_array($node->getType(), $pageArray)) {
      $this->logAuditMessage($node->getTitle());
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Log the path and user email.
   *
   * @param string $info
   *   The path info to include in the message.
   */
  private function logAuditMessage($info) {
    $userEmail = $this->currentUser->getEmail();
    $msg = sprintf('Potential fetch of personal data from page: %s by %s', $info, $userEmail);
    $this->auditLogger->info('Lookup', $msg);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::CONTROLLER][] = ['auditOnRouteChange', 1000];
    return $events;
  }

}
