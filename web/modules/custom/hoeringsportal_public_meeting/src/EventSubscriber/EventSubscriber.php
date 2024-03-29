<?php

namespace Drupal\hoeringsportal_public_meeting\EventSubscriber;

use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * An event subscriber.
 */
class EventSubscriber implements EventSubscriberInterface {

  /**
   * Handler for the KernelEvents::REQUEST event.
   *
   * On the "/begivenheder" page
   * When the user selects "finished" in "field_content_state_value"
   * We want to default to "field_reply_deadline_value DESC" as sorting.
   */
  public function setDefaultSorting(RequestEvent $event) {
    $request = $event->getRequest();

    if ('/begivenheder' === $request->getPathInfo()) {
      $contentState = $request->query->get('field_content_state_value');
      if ('finished' === $contentState) {
        $referer = $request->headers->get('referer');
        if (!empty($referer)) {
          $info = UrlHelper::parse($referer);
          $previousContentStateValue = $info['query']['field_content_state_value'] ?? NULL;

          $sortKey = 'sort_bef_combine';
          $defaultSort = 'field_last_meeting_time_value DESC';
          $currentSort = $request->query->get($sortKey);

          if ($previousContentStateValue !== $contentState && $defaultSort !== $currentSort) {
            $path = $request->getPathInfo();
            $query = $request->query->all();
            $query[$sortKey] = $defaultSort;
            $url = $path . '?' . UrlHelper::buildQuery($query);
            $event->setResponse(new RedirectResponse($url));
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => [['setDefaultSorting']],
    ];
  }

}
