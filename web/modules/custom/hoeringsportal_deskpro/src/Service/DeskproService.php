<?php

namespace Drupal\hoeringsportal_deskpro\Service;

use Deskpro\API\APIResponse;
use Deskpro\API\DeskproClient;
use Deskpro\API\Exception\APIException;
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
   * Deskpro client.
   *
   * @var \Deskpro\API\DeskproClient*/
  private $client;

  /**
   * Constructs a new DeskproService object.
   */
  public function __construct(Settings $settings) {
    $this->configuration = $settings->get('hoeringsportal_deskpro.deskpro');
  }

  /**
   * Get hearings.
   */
  public function getHearings() {
    try {
      $response = $this->get('/ticket_custom_fields/{id}', ['id' => $this->configuration['hearing_field_id']]);

      return $response;
    }
    catch (APIException $e) {
      throw new DeskproException($e->getMessage(), 0, $e);
    }
  }

  /**
   * Get tickets from a hearing.
   *
   * @param int $hearingId
   *   A hearing id.
   *
   * @return \Deskpro\API\APIResponseInterface
   *   The tickets.
   *
   * @throws \Drupal\hoeringsportal_deskpro\Exception\DeskproException
   */
  public function getTickets($hearingId) {
    try {
      $response = $this->get('/tickets', ['ticket_field.' . $this->configuration['hearing_field_id'] => $hearingId]);

      return $response;
    }
    catch (APIException $e) {
      throw new DeskproException('', 0, $e);
    }
  }

  /**
   * Get a ticket.
   *
   * @param int $ticketId
   *   A ticket id.
   *
   * @return \Deskpro\API\APIResponseInterface
   *   The ticket.
   *
   * @throws \Drupal\hoeringsportal_deskpro\Exception\DeskproException
   */
  public function getTicket($ticketId) {
    try {
      $response = $this->get('/tickets/{ticket}', ['ticket' => $ticketId]);
      $data = $response->getData();
      $data['person'] = $this->getPerson($data['person'])->getData();

      return $response;
    }
    catch (APIException $e) {
      throw new DeskproException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * Get attachments from a ticket.
   *
   * @param int $ticketId
   *   A ticket id.
   *
   * @return \Deskpro\API\APIResponseInterface
   *   The attachments.
   *
   * @throws \Drupal\hoeringsportal_deskpro\Exception\DeskproException
   */
  public function getTicketAttachments($ticketId) {
    try {
      $response = $this->get('/tickets/{ticket}/attachments', ['ticket' => $ticketId]);

      return $response;
    }
    catch (APIException $e) {
      throw new DeskproException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * Get messages from a ticket.
   *
   * @param int $ticketId
   *   A ticket id.
   * @param bool $noCache
   *   Disable cache if set.
   *
   * @return \Deskpro\API\APIResponseInterface
   *   The messages.
   *
   * @throws \Drupal\hoeringsportal_deskpro\Exception\DeskproException
   */
  public function getTicketMessages($ticketId, $noCache = FALSE) {
    try {
      $response = $this->get('/tickets/{ticket}/messages', ['ticket' => $ticketId], $noCache);

      // Filter out agent notes.
      $data = array_filter($response->getData(), function ($message) {
        return $message['is_agent_note'] === 0;
      });
      $response = new ApiResponse($data, $response->getMeta(), $response->getLinked());

      return $response;
    }
    catch (APIException $e) {
      throw new DeskproException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * Get message attachments.
   */
  public function getMessageAttachments($message) {
    $response = $this->get('tickets/' . $message['ticket'] . '/messages/' . $message['id'] . '/attachments');

    return $response;
  }

  /**
   * Get a person by id.
   */
  public function getPerson($id) {
    $response = $this->get('people/' . $id);

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
   */
  public function getTicketEmbedForm($departmentId, $hearingId, array $defaultValues = NULL) {
    $id = uniqid('deskpro_ticket_form', TRUE);
    $embed_options = [
      'helpdeskUrl' => $this->configuration['deskpro_url'],
      'containerId' => $id,
      'type' => 'form',
      'language' => 'da_DK',
      'department' => $departmentId,
      'hide_department' => 1,
      // 'width' => '100%',.
      'default_values' => $defaultValues,
    ];

    return implode('', [
      // '<pre>'.json_encode($embed_options, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'</pre>',.
      '<div id="' . htmlspecialchars($id) . '"></div>',
      '<script type="text/javascript">window.DESKPRO_EMBED_OPTIONS = ' . json_encode($embed_options) . ';</script>',
      '<script type="text/javascript" src="' . htmlspecialchars($this->configuration['deskpro_url'] . '/dyn-assets/pub/build/embed_loader.js') . '"></script>',
    ]);
  }

  /**
   * Convenience function for getting data from the Deskpro client.
   */
  private function get($endpoint, array $params = [], $noCache = FALSE) {
    $cache = \Drupal::cache('data');
    $cacheKey = __METHOD__ . '||' . json_encode(func_get_args());
    $cacheTtl = $this->configuration['cache_ttl'];

    if (!$noCache && $cacheTtl > 0) {
      if ($cacheItem = $cache->get($cacheKey)) {
        return new ApiResponse($cacheItem->data['data'], $cacheItem->data['meta'], $cacheItem->data['linked']);
      }
    }

    $response = $this->getClient()->get($endpoint, $params);
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

}
