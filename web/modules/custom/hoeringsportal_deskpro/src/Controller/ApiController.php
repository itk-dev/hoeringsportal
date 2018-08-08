<?php

namespace Drupal\hoeringsportal_deskpro\Controller;

use Deskpro\API\APIResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\hoeringsportal_deskpro\Exception\DeskproException;
use Drupal\hoeringsportal_deskpro\Service\DeskproService;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiController.
 */
class ApiController extends ControllerBase {

  /**
   * Drupal\hoeringsportal_deskpro\Service\DeskproService definition.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\DeskproService
   */
  protected $deskpro;

  /**
   * Constructs a new DeskproController object.
   */
  public function __construct(DeskproService $deskpro) {
    $this->deskpro = $deskpro;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('hoeringsportal_deskpro.deskpro')
    );
  }

  /**
   * Api index.
   */
  public function index() {
    return $this->redirect('hoeringsportal_deskpro.api_controller_docs');
  }

  /**
   * Api docs.
   */
  public function docs() {
    $templatePath = drupal_get_path('module', 'hoeringsportal_deskpro') . '/templates/api/docs.html.twig';
    $template = \Drupal::service('twig')->load($templatePath);
    $content = $template->render();

    return new Response($content);
  }

  /**
   * Departments.
   */
  public function departments(Request $request) {
    try {
      $query = $this->getDeskproQuery($request);
      $departments = $this->deskpro->getTicketDepartments($query);

      return $this->createResponse($departments);
    }
    catch (DeskproException $exception) {
      return $this->createErrorResponse($exception);
    }
  }

  /**
   * Get all hearings.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function hearings(Request $request) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'hearing')
      ->condition('status', NodeInterface::PUBLISHED)
      // @TODO Get only active hearings.
      ->sort('title', 'ASC');

    if ($name = $request->query->get('name')) {
      $query->condition('title', '%' . $name . '%', 'LIKE');
    }
    $nids = $query->execute();
    $hearings = Node::loadMultiple($nids);

    $data = array_values(array_map(function (NodeInterface $hearing) {
      return [
        'id' => $hearing->field_hearing_id->value,
        'title' => $hearing->getTitle(),
        'field_reply_deadline' => $hearing->field_reply_deadline->value,
      ];
    }, $hearings));

    return new JsonResponse($data);
  }

  /**
   * Get hearing tickets.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function hearingTickets(Request $request, $hearing) {
    try {
      $query = $this->getDeskproQuery($request);
      $tickets = $this->deskpro->getHearingTickets($hearing, $query);

      return $this->createResponse($tickets);
    }
    catch (DeskproException $exception) {
      return $this->createErrorResponse($exception);
    }
  }

  /**
   * Get all tickets.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function tickets(Request $request) {
    try {
      $query = $this->getDeskproQuery($request);
      $tickets = $this->deskpro->getTickets($query);

      return $this->createResponse($tickets);
    }
    catch (DeskproException $exception) {
      return $this->createErrorResponse($exception);
    }
  }

  /**
   * Get ticket data.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $ticket
   *   The ticket id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function ticket(Request $request, $ticket) {
    try {
      $query = $this->getDeskproQuery($request);
      $ticket = $this->deskpro->getTicket($ticket, $query);

      $data = $ticket->getData();
      // $data['person'] = $this->getPerson($data['person']);.
      return new JsonResponse($data);
    }
    catch (DeskproException $exception) {
      return $this->createErrorResponse($exception);
    }
  }

  /**
   * Get ticket attachments.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function ticketAttachments(Request $request, $ticket) {
    try {
      $query = $this->getDeskproQuery($request);
      $attachments = $this->deskpro->getTicketAttachments($ticket, $query);

      return $this->createResponse($attachments);
    }
    catch (DeskproException $exception) {
      return $this->createErrorResponse($exception);
    }
  }

  /**
   * Get ticket messages.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function ticketMessages(Request $request, $ticket) {
    try {
      $query = $this->getDeskproQuery($request);
      $messages = $this->deskpro->getTicketMessages($ticket, $query);

      return $this->createResponse($messages);
    }
    catch (DeskproException $exception) {
      return $this->createErrorResponse($exception);
    }
  }

  /**
   * Get query to pass on to api.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array
   *   The query.
   */
  private function getDeskproQuery(Request $request) {
    return array_filter($request->query->all());
  }

  /**
   * Create a json response from a Deskpro api response.
   */
  private function createResponse(APIResponse $response) {
    return new JsonResponse([
      'data' => $response->getData(),
      'meta' => $response->getMeta(),
      'linked' => $response->getLinked(),
    ]);
  }

  /**
   * Create an error response.
   */
  private function createErrorResponse(\Exception $exception) {
    $message = $exception->getMessage() ?? ($exception->getPrevious() ? $exception->getPrevious()
      ->getMessage() : 'unknown error');

    return new JsonResponse(['error' => $message], 400);
  }

}
