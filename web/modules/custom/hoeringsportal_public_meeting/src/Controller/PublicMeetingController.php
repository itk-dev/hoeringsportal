<?php

namespace Drupal\hoeringsportal_public_meeting\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\hoeringsportal_public_meeting\Helper\PublicMeetingHelper;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Public meeting controller.
 */
final class PublicMeetingController extends ControllerBase {
  use AutowireTrait;

  /**
   * Must match the delta parameter name in the route.
   */
  public const string DATES_DELTA = 'dates_delta';

  public function __construct(
    #[Autowire(service: 'hoeringsportal_public_meeting.public_meeting_helper')]
    private readonly PublicMeetingHelper $helper,
    private readonly RouteMatchInterface $routeMatch,
  ) {}

  /**
   * Show date.
   */
  public function showDate(NodeInterface $node, int $dates_delta) {
    if (!$this->helper->isPublicMeeting($node)) {
      // This should never happen due to how the node route parameter is
      // defined, but better safe than sorry.
      throw new NotFoundHttpException();
    }

    /** @var \Drupal\Core\Field\FieldItemListInterface $dates */
    $dates = $node->get('field_pretix_dates');

    if ($dates_delta > -1 && $dates_delta >= $dates->count()) {
      throw new NotFoundHttpException();
    }

    return \Drupal::entityTypeManager()->getViewBuilder('node')->view($node);
  }

}
