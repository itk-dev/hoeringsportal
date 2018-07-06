<?php

namespace Drupal\hoeringsportal_deskpro\Service;

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
   * Get messages from a ticket.
   *
   * @param int $ticketId
   *   A ticket id.
   *
   * @return \Deskpro\API\APIResponseInterface
   *   The messages.
   *
   * @throws \Drupal\hoeringsportal_deskpro\Exception\DeskproException
   */
  public function getTicketMessages($ticketId) {
    try {
      $response = $this->get('/tickets/{ticket}/messages', ['ticket' => $ticketId]);

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
   * Validate Deskpro configuration.
   */
  public function validateConfiguration() {
    foreach (['deskpro_url', 'api_code_key', 'hearing_field_id'] as $key) {
      if (empty($this->configuration[$key])) {
        throw new DeskproException('"' . $key . '" is missing or empty');
      }
    }
  }

  /**
   * Convenience function for getting data from the Deskpro client.
   */
  private function get($endpoint, array $params = []) {
    return $this->getClient()->get($endpoint, $params);
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
