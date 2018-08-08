<?php

namespace Drupal\hoeringsportal_deskpro\Service;

use Deskpro\API\APIResponse;
use Deskpro\API\DeskproClient;
use Deskpro\API\Exception\APIException;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Site\Settings;
use Drupal\hoeringsportal_deskpro\Exception\DeskproException;

/**
 * Class DeskproService.
 */
class DeskproService {
  /**
   * Deskpro configuration.
   *
   * @var array
   */
  private $configuration;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * Constructs a new DeskproService object.
   */
  public function __construct(Settings $settings, LanguageManagerInterface $languageManager) {
    $this->configuration = $settings->get('hoeringsportal_deskpro.deskpro');
    $this->languageManager = $languageManager;
  }

  /**
   * Get hearings.
   */
  public function getHearings(array $query = []) {
    try {
      $response = $this->get('/ticket_custom_fields/{id}', ['id' => $this->configuration['hearing_field_id']]);

      return $response;
    }
    catch (APIException $e) {
      throw $this->createException($e);
    }
  }

  /**
   * Get tickets from a hearing.
   *
   * @param string $hearingId
   *   The hearing id.
   * @param array $query
   *   The query.
   *
   * @return \Deskpro\API\APIResponse|\Deskpro\API\APIResponseInterface
   *   The api response.
   */
  public function getHearingTickets($hearingId, array $query = []) {
    $query['ticket_field.' . $this->configuration['hearing_field_id']] = $hearingId;

    return $this->getTickets($query);
  }

  /**
   * Get tickets.
   */
  public function getTickets(array $query = []) {
    try {
      $response = $this->get('/tickets', $query);

      $data = $response->getData();
      $this->expandData($data, $query);

      return $this->setResponseData($response, $data);
    }
    catch (APIException $e) {
      throw $this->createException($e);
    }
  }

  /**
   * Get a ticket.
   *
   * @param int $ticketId
   *   A ticket id.
   * @param array $query
   *   The query.
   *
   * @return \Deskpro\API\APIResponseInterface
   *   The ticket.
   *
   * @throws \Drupal\hoeringsportal_deskpro\Exception\DeskproException
   */
  public function getTicket($ticketId, array $query = []) {
    try {
      $response = $this->get('/tickets/{ticket}', ['ticket' => $ticketId]);

      $data = $response->getData();
      $this->expandData($data, $query);

      return $this->setResponseData($response, $data);
    }
    catch (APIException $e) {
      throw $this->createException($e);
    }
  }

  /**
   * Get attachments from a ticket.
   *
   * @param int $ticketId
   *   A ticket id.
   * @param array $query
   *   The query.
   *
   * @return \Deskpro\API\APIResponseInterface
   *   The attachments.
   *
   * @throws \Drupal\hoeringsportal_deskpro\Exception\DeskproException
   */
  public function getTicketAttachments($ticketId, array $query = []) {
    try {
      $query['ticket'] = $ticketId;
      $response = $this->get('/tickets/{ticket}/attachments', $query);

      $data = $response->getData();
      // Filter out agent notes.
      $data = array_values(array_filter($data, function ($message) {
        return !$message['is_agent_note'];
      }));

      return $this->setResponseData($response, $data);
    }
    catch (APIException $e) {
      throw $this->createException($e);
    }
  }

  /**
   * Get ticket deparments.
   *
   * @param array $query
   *   The query.
   *
   * @return \Deskpro\API\APIResponseInterface
   *   The messages.
   *
   * @throws \Drupal\hoeringsportal_deskpro\Exception\DeskproException
   */
  public function getTicketDepartments(array $query = []) {
    try {
      $response = $this->get('/ticket_departments', $query);

      $data = $response->getData();

      $this->expandData($data, $query);

      return $this->setResponseData($response, $data);
    }
    catch (APIException $e) {
      throw $this->createException($e);
    }
  }

  /**
   * Get messages from a ticket.
   *
   * @param int $ticketId
   *   A ticket id.
   * @param array $query
   *   The query.
   *
   * @return \Deskpro\API\APIResponseInterface
   *   The messages.
   *
   * @throws \Drupal\hoeringsportal_deskpro\Exception\DeskproException
   */
  public function getTicketMessages($ticketId, array $query = []) {
    try {
      $query['ticket'] = $ticketId;
      $response = $this->get('/tickets/{ticket}/messages', $query);

      $data = $response->getData();

      // Filter out agent notes.
      $data = array_values(array_filter($data, function ($message) {
        return !$message['is_agent_note'];
      }));

      $this->expandData($data, $query);

      return $this->setResponseData($response, $data);
    }
    catch (APIException $e) {
      throw $this->createException($e);
    }
  }

  /**
   * Get message attachments.
   */
  public function getMessageAttachments($message, array $query = []) {
    $query['ticket'] = $message['ticket'];
    $query['id'] = $message['id'];
    $response = $this->get('tickets/{ticket}/messages/{id}/attachments', $query);

    return $response;
  }

  /**
   * Get a person by id.
   */
  public function getPerson($id, array $query = []) {
    $query['id'] = $id;
    $response = $this->get('people/{id}', $query);

    return $response;
  }

  /**
   * Validate Deskpro configuration.
   */
  public function validateConfiguration() {
    $requiredKeys = [
      'deskpro_url',
      'api_code_key',
      'hearing_field_id',
      'hearing_department_id',
      'cache_ttl',
    ];
    foreach ($requiredKeys as $key) {
      if (empty($this->configuration[$key])) {
        throw new DeskproException('"' . $key . '" is missing or empty');
      }
    }
  }

  /**
   * Get ticket embed form.
   *
   * The form generated in Deskpro
   * (cf. https://example.deskpro.com/agent/#admin:/tickets/ticket_deps/1)
   * does not handle default values, so we use embed_loader.js directly.
   *
   * https://example.deskpro.com/agent/#admin:/portal/1/ticket_form_widget
   */
  public function getTicketEmbedForm($departmentId, $hearingId, array $defaultValues = NULL) {
    $id = uniqid('deskpro_ticket_form', TRUE);
    $embed_options = [
      'helpdeskUrl' => $this->configuration['deskpro_url'],
      'containerId' => $id,
      'type' => 'form',
      'language' => $this->languageManager->getCurrentLanguage()->getId(),
      'department' => $departmentId,
      'hide_department' => 1,
      'default_values' => $defaultValues,
    ];

    return implode('', [
      '<div id="' . htmlspecialchars($id) . '"></div>',
      '<script type="text/javascript">window.DESKPRO_EMBED_OPTIONS = ' . json_encode($embed_options) . ';</script>',
      '<script type="text/javascript" src="' . htmlspecialchars($this->configuration['deskpro_url'] . '/dyn-assets/pub/build/embed_loader.js') . '"></script>',
    ]);
  }

  /**
   * Simple per request response cache.
   *
   * @var array
   */
  private $responseCache = [];

  /**
   * Convenience function for getting data from the Deskpro client.
   */
  private function get($endpoint, array $query = []) {
    $cache = \Drupal::cache('data');
    $cacheKey = __METHOD__ . '||' . json_encode(func_get_args());
    $cacheTtl = $this->configuration['cache_ttl'];

    $noCache = isset($query['no_cache']);

    if (!$noCache && $cacheTtl > 0) {
      if ($cacheItem = $cache->get($cacheKey)) {
        return new ApiResponse($cacheItem->data['data'], $cacheItem->data['meta'], $cacheItem->data['linked']);
      }
    }

    // We don't want to performs the same api request more than once pr. http
    // request.
    if (isset($this->responseCache[$cacheKey])) {
      return $this->responseCache[$cacheKey];
    }

    // Trim out our custom query parameters.
    unset($query['expand']);
    unset($query['no_cache']);

    $response = $this->getClient()->get($endpoint, $query);
    $this->responseCache[$cacheKey] = $response;

    $data = [
      'data' => $response->getData(),
      'meta' => $response->getMeta(),
      'linked' => $response->getLinked(),
    ];
    $cache->set($cacheKey, $data, time() + $cacheTtl);

    return $response;
  }

  /**
   * Get a Deskpro client.
   */
  private function getClient() {
    // https://github.com/deskpro/deskpro-api-client-php
    $this->validateConfiguration();
    $client = new DeskproClient($this->configuration['deskpro_url']);
    $authKey = explode(':', $this->configuration['api_code_key']);
    $client->setAuthKey(...$authKey);

    return $client;
  }

  /**
   * Set data in response object.
   *
   * @param \Deskpro\API\APIResponse $response
   *   The api response.
   * @param array $data
   *   The new data.
   *
   * @return \Deskpro\API\APIResponse
   *   The api response with updated data.
   */
  private function setResponseData(APIResponse $response, array $data) {
    return new APIResponse($data, $response->getMeta(), $response->getLinked());
  }

  /**
   * Expand data depending on query parameters.
   */
  private function expandData(array &$data, array $query) {
    if (isset($query['expand'])) {
      $expands = [
        'person' => function (array &$item) use ($query) {
          if (isset($item['person'])) {
            $person = $this->getPerson($item['person'], $query);
            $item['person'] = $person !== NULL ? $person->getData() : NULL;
          }
        },
        'attachments' => function (array &$item) use ($query) {
          if (isset($item['attachments'])) {
            $attachments = $this->getMessageAttachments($item, $query);
            $item['attachments'] = $attachments !== NULL ? $attachments->getData() : NULL;
          }
        },
        'fields' => function (array &$item) {
          if (isset($this->configuration['ticket_custom_fields'])) {
            $fields = $this->configuration['ticket_custom_fields'];
            if (is_array($fields)) {
              foreach ($fields as $id => $name) {
                if (isset($item['fields'][$id]['value'])) {
                  $item['fields'][$name] = $item['fields'][$id]['value'];
                }
              }
            }
          }
        },
      ];

      $fields = array_map('trim', explode(',', $query['expand']));
      foreach ($fields as $field) {
        if (isset($expands[$field])) {
          $expand = $expands[$field];
          $expand($data);
          foreach ($data as &$item) {
            if (is_array($item)) {
              $expand($item);
            }
          }
        }
      }
    }
  }

  /**
   * Create exception wrapping an api exception.
   */
  private function createException(APIException $exception) {
    return new DeskproException($exception->getMessage(), $exception->getCode(), $exception);
  }

}
