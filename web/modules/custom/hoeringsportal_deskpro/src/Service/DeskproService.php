<?php

namespace Drupal\hoeringsportal_deskpro\Service;

use Deskpro\API\APIResponse;
use Deskpro\API\DeskproClient;
use Deskpro\API\Exception\APIException;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\hoeringsportal_deskpro\Exception\DeskproException;
use Drupal\hoeringsportal_deskpro\State\DeskproConfig;
use GuzzleHttp\Client;

/**
 * TODO: Description of what the class does.
 */
class DeskproService {
  /**
   * The Deskpro config.
   *
   * @var \Drupal\hoeringsportal_deskpro\State\DeskproConfig
   */
  private $config;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private $languageManager;

  /**
   * The client.
   *
   * @var \GuzzleHttp\Client
   */
  private $client;

  /**
   * Constructs a new DeskproService object.
   */
  public function __construct(DeskproConfig $config, LanguageManagerInterface $languageManager) {
    $this->config = $config;
    $this->languageManager = $languageManager;
  }

  /**
   * Get hearings.
   */
  public function getHearings(array $query = []) {
    try {
      $response = $this->get('/ticket_custom_fields/{id}', ['id' => $this->getTicketHearingIdFieldId()]);

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
    $query['ticket_field.' . $this->getTicketHearingIdFieldId()] = $hearingId;

    return $this->getTickets($query);
  }

  /**
   * Get tickets.
   */
  public function getTickets(array $query = []) {
    try {
      // Default sorting.
      $query += [
        'order_by' => 'date_created',
        'order_dir' => 'desc',
      ];
      $response = $this->get('/tickets', $query);
      // Ordering and pagination does not make sense for sub-queries.
      unset($query['order_by'], $query['order_dir'], $query['count'], $query['page']);

      $data = $response->getData();
      $data = $this->filterTickets($data);

      $this->expandData($data, $query, $response);

      if ($this->getExpand($query, 'messages')) {
        foreach ($data as &$ticket) {
          $messages = $this->getTicketMessages($ticket['id'], $query);
          $ticket['messages'] = $messages->getData();
        }
      }

      if ($this->getExpand($query, 'attachments')) {
        foreach ($data as &$ticket) {
          $attachments = $this->getTicketAttachments($ticket['id'], $query);
          $ticket['attachments'] = $attachments->getData();
        }
      }

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
      $this->expandData($data, $query, $response);

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
   * @param bool $noFilter
   *   If set, all departments will be returned.
   *
   * @return \Deskpro\API\APIResponseInterface
   *   The messages.
   *
   * @throws \Drupal\hoeringsportal_deskpro\Exception\DeskproException
   */
  public function getTicketDepartments(array $query = [], $noFilter = FALSE) {
    try {
      $response = $this->get('/ticket_departments', $query);

      $data = $response->getData();

      if (!$noFilter) {
        // Filter out unavailable departments.
        $availableIds = $this->config->getAvailableDepartments();
        $data = array_filter($data,
          function (array $department) use ($availableIds) {
            return in_array($department['id'], $availableIds);
          });
      }

      return $this->setResponseData($response, $data);
    }
    catch (APIException $e) {
      throw $this->createException($e);
    }
  }

  /**
   * Get ticket custom field "representation".
   */
  public function getRepresentations() {
    try {
      $data = $this->getTicketCustomField('representation');

      return $data['choices'] ?? [];
    }
    catch (\Exception $exception) {
      return [];
    }
  }

  /**
   * Get ticket custom fields.
   */
  public function getAllTicketCustomFields() {
    $response = $this->get('/ticket_custom_fields');

    return $response->getData();
  }

  /**
   * Get ticket custom field.
   */
  public function getTicketCustomField($name) {
    $id = $this->config->getTicketCustomFieldId($name);
    $response = $this->get('/ticket_custom_fields/{id}', [
      'id' => $id,
      'no_cache' => TRUE,
    ]);

    return $response->getData();
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

      $this->expandData($data, $query, $response);

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
   * Create a person.
   */
  public function createPerson(array $data) {
    $personData = [
      'name' => $data['name'],
      'primary_email' => $data['email'],
    ];

    $endpoint = '/people';

    // Check if person exists.
    $response = $this->client()->get($endpoint, ['primary_email' => $personData['primary_email']]);
    $data = $response->getData();
    if (!empty($data)) {
      // Create new response with just a single person.
      return new APIResponse(reset($data), [], []);
    }

    $response = $this->client()->post($endpoint, $personData);

    return $response;
  }

  /**
   * Create a ticket.
   */
  public function createTicket(array $person, array $data) {
    $ticketData = $this->filterData($data, [
      'language',
      'department',
      'agent',
      'fields',
      'subject',
    ]);
    $ticketData['person']['id'] = $person['id'];
    $ticketData['suppress_user_notify'] = FALSE;

    $endpoint = '/tickets';

    $response = $this->client()->post($endpoint, $ticketData);

    return $response;
  }

  /**
   * Create a message on a ticket.
   */
  public function createMessage(array $ticket, array $data, array $blobs = []) {
    $messageData = $this->filterData($data, ['message']);
    $messageData['person']['id'] = $ticket['person'];
    if ($blobs) {
      $messageData['attachments'] = $this->getAttachments($blobs);
    }

    $endpoint = '/tickets/{parentId}/messages';
    $response = $this->client()->post($endpoint, $messageData, [
      'parentId' => $ticket['id'],
    ]);

    return $response;
  }

  /**
   * Upload files.
   */
  public function uploadFiles(array $files) {
    return array_map(function ($path) {
      $response = $this->uploadFile($path);
      return $response->getData();
    }, $files);
  }

  /**
   * Get attachments from blobs.
   */
  public function getAttachments(array $blobs, $inline = FALSE) {
    $attachments = [];

    foreach ($blobs as $blob) {
      $attachments[] = [
        'blob_auth' => $blob['blob_auth'],
        'is_inline' => $inline,
      ];
    }

    return $attachments;
  }

  /**
   * Upload a file.
   */
  private function uploadFile($path) {
    $endpoint = '/blobs/temp';
    $response = $this->client()->post($endpoint, [
      'multipart' => [
        [
          'name'     => 'file',
          'filename' => basename($path),
          'contents' => fopen($path, 'r'),
        ],
      ],
    ]);

    return $response;
  }

  /**
   * Filter data by keys.
   */
  private function filterData(array $data, array $fields) {
    return array_filter(
      $data,
      function ($key) use ($fields) {
        return in_array($key, $fields);
      },
      ARRAY_FILTER_USE_KEY
    );
  }

  /**
   * Get last response exception from Deskpro API.
   */
  public function getLastHttpRequestException() {
    return $this->client()->getLastHTTPRequestException();
  }

  /**
   * Get Deskpro url.
   */
  public function getDeskproUrl($path, $params) {
    return $this->client()->getHelpdeskUrl()
      . $this->client()->getURLInterpolator()->interpolate($path, $params);
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
  public function getTicketEmbedForm($departmentId, $hearingId, array $defaultValues = []) {
    $defaultValues['ticket']['department'] = $departmentId;
    $defaultValues['ticket']['ticket_field_' . $this->getTicketHearingIdFieldId()]['data'] = $hearingId;

    $id = uniqid('deskpro_ticket_form', TRUE);
    $embed_options = [
      'helpdeskUrl' => $this->config->getDeskproUrl(),
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
      '<script type="text/javascript" src="' . htmlspecialchars($this->config->getDeskproUrl() . '/dyn-assets/pub/build/embed_loader.js') . '"></script>',
    ]);
  }

  /**
   * Check that token is a valid data token.
   */
  public function isValidToken(string $token = NULL) {
    return $token === $this->getToken();
  }

  /**
   * Get the Deskpro token.
   */
  public function getToken() {
    return $this->config->getDataToken();
  }

  /**
   * Get hearing id field id.
   */
  public function getTicketHearingIdFieldId() {
    return $this->getTicketFieldId('hearing_id');
  }

  /**
   * Get hearing id field id.
   */
  public function getTicketFieldId(string $field) {
    return $this->config->getTicketCustomFieldId($field);
  }

  /**
   * Get ticket custom fields.
   */
  public function getTicketCustomFields() {
    return $this->config->getTicketCustomFields();
  }

  /**
   * Get agents.
   */
  public function getAgents() {
    return $this->get('agents');
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
    $cacheTtl = $this->config->getCacheTtl();

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

    if (isset($query['expand'])) {
      // https://deskpro.gitbook.io/dev-guide/api-basics/sideloading.
      $names = is_array($query['expand']) ? $query['expand'] : preg_split('/,/', $query['expand'], -1, PREG_SPLIT_NO_EMPTY);
      $names = array_map([$this, 'getIncludeName'], $names);
      $query['include'] = implode(',', $names);
    }
    // Trim out our custom query parameters.
    unset($query['expand']);
    unset($query['no_cache']);

    $response = $this->client()->get($endpoint, $query);
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
   * Get Deskpro include name.
   *
   * (cf. https://deskpro.gitbook.io/dev-guide/api-basics/sideloading)
   */
  private function getIncludeName($name) {
    switch ($name) {
      case 'attachments':
        return 'ticket_attachment';
    }

    return $name;
  }

  /**
   * Get a Deskpro client.
   */
  private function client() {
    if (NULL === $this->client) {
      // https://github.com/deskpro/deskpro-api-client-php
      $authKey = $this->config->getDeskproApiCodeKey();
      $client = new Client(['connect_timeout' => 2]);
      $this->client = new DeskproClient($this->config->getDeskproUrl(), $client);
      $this->client->setAuthKey(...$authKey);
    }

    return $this->client;
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
   * Get info on what to expand.
   */
  private function getExpand(array $query, string $field = NULL) {
    if (!isset($query['expand'])) {
      return NULL;
    }

    $expand = $query['expand'];
    if (!is_array($expand)) {
      $expand = explode(',', $expand);
    }
    $fields = array_map('trim', $expand);

    return NULL === $field ? $fields : in_array($field, $fields);
  }

  /**
   * Expand data depending on query parameters.
   */
  private function expandData(array &$data, array $query, APIResponse $response) {
    $expands = [
      'person' => function (array &$item) use ($query, $response) {
        if (isset($item['person'])) {
          $linked = $response->getLinked();
          $includeName = $this->getIncludeName('person');
          if (isset($linked[$includeName][$item['person']])) {
            $item['person'] = $linked[$includeName][$item['person']];
          }
          else {
            $person = $this->getPerson($item['person'], $query);
            $item['person'] = $person !== NULL ? $person->getData() : NULL;
          }
        }
      },
      'attachments' => function (array &$item) use ($query, $response) {
        if (isset($item['attachments'])) {
          $linked = $response->getLinked();
          $includeName = $this->getIncludeName('attachments');
          if (isset($linked[$includeName])) {
            $attachments = $linked[$includeName];
            $item['attachments'] = array_map(function ($id) use ($attachments) {
              return $attachments[$id];
            }, $item['attachments']);
          }
          else {
            $attachments = $this->getMessageAttachments($item, $query);
            $item['attachments'] = $attachments !== NULL ? $attachments->getData() : NULL;
          }
        }
      },
      'fields' => function (array &$item) {
        foreach ($this->config->getTicketCustomFields() as $name => $id) {
          if (isset($item['fields'][$id]['detail'])) {
            $item['fields'][$name] = $item['fields'][$id]['detail'];
          }
          elseif (isset($item['fields'][$id]['value'])) {
            $item['fields'][$name] = $item['fields'][$id]['value'];
          }
        }
      },
    ];

    $fields = $this->getExpand($query);
    if (is_array($fields)) {
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
   *
   * @param \Deskpro\API\Exception\APIException $exception
   *   An exception.
   *
   * @return \Drupal\hoeringsportal_deskpro\Exception\DeskproException
   *   The exception.
   */
  private function createException(APIException $exception) {
    return new DeskproException($exception->getMessage(), $exception->getCode(), $exception);
  }

  /**
   * Return config.
   *
   * @return \Drupal\hoeringsportal_deskpro\State\DeskproConfig
   *   The config.
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * Get Deskpro languages.
   */
  public function getLanguages(array $query = []) {
    $endpoint = '/languages';
    $response = $this->client()->get($endpoint, $query);

    return $response;
  }

  /**
   * Filter tickets.
   */
  private function filterTickets(array $data) {
    // Remove tickets that should not be published.
    $dontPublishFieldId = $this->config->getTicketCustomFieldId('unpublish_reply');
    $data = array_values(array_filter($data, function ($ticket) use ($dontPublishFieldId) {

      if (isset($ticket['fields'][$dontPublishFieldId])) {
        return 1 != $ticket['fields'][$dontPublishFieldId]['value'];
      }

      return TRUE;
    }));

    return $data;
  }

}
