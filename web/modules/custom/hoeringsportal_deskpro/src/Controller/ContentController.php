<?php

namespace Drupal\hoeringsportal_deskpro\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ContentController.
 */
class ContentController extends ControllerBase {

  /**
   * The Deskpro service.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\DeskproService
   */
  protected $deskpro;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new DeskproController object.
   */
  public function __construct(DeskproService $deskpro, RendererInterface $renderer) {
    $this->deskpro = $deskpro;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_deskpro.deskpro'),
      $container->get('renderer')
    );
  }

  /**
   * Department.
   */
  public function hearingTickets(NodeInterface $node) {
    if ('hearing' !== $node->bundle()) {
      return NULL;
    }

    $tickets = $this->getTickets($node);
    $build = [
      '#theme' => 'hoeringsportal_hearing_tickets',
      '#node' => $node,
      '#is_loading' => FALSE,
      '#tickets' => $tickets,
    ];

    return new Response($this->renderer->render($build));
  }

  /**
   * Get tickets.
   */
  private function getTickets(NodeInterface $hearing) {
    $tickets = $this->deskpro->getHearingTickets(
      $hearing->field_deskpro_hearing_id->value,
      ['expand' => ['person', 'messages']]
    )->getData();

    return $tickets;
  }

}
