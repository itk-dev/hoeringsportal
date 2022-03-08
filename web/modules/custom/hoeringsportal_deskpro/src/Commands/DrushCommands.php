<?php

namespace Drupal\hoeringsportal_deskpro\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper;
use Drupal\node\Entity\Node;
use Drush\Commands\DrushCommands as BaseDrushCommands;
use Symfony\Component\Console\Exception\RuntimeException;

/**
 * Custom drush commands from hoeringsportal_deskpro.
 */
class DrushCommands extends BaseDrushCommands {
  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Deskpro helper.
   *
   * @var \Drupal\hoeringsportal_deskpro\Service\HearingHelper
   */
  private $helper;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, HearingHelper $helper) {
    parent::__construct();
    $this->entityTypeManager = $entityTypeManager;
    $this->helper = $helper;
  }

  /**
   * Synchronizes hearing ticket data with Deskpro.
   *
   * @param int $hearingId
   *   Hearing id.
   * @param int $ticketId
   *   Ticket id.
   *
   * @command hoeringsportal:deskpro:synchronize-hearing-ticket
   * @usage hoeringsportal:deskpro:synchronize-hearing-ticket 123 456
   *   Refreshes Deskpro data for ticket 456 on hearing 123.
   */
  public function synchronizeHearingTicket(int $hearingId, int $ticketId) {
    $payload = $this->helper->getTicketSynchronizationPayload($hearingId, $ticketId);

    $result = $this->helper->runSynchronizeTicket($payload);
    $this->output->writeln([
      'Result',
      json_encode($result, JSON_PRETTY_PRINT),
      str_repeat('-', 80),
    ]);
  }

  /**
   * Synchronizes hearing tickets data with Deskpro.
   *
   * @param string|null $ids
   *   Comma-list of hearing ids.
   *
   * @command hoeringsportal:deskpro:synchronize-hearing-tickets
   * @usage hoeringsportal:deskpro:synchronize-hearing-tickets 42,87
   *   Refreshes Deskpro data for hearings with ids 42 and 87.
   */
  public function synchronizeHearingTickets($ids) {
    $ids = preg_split('/\s*,\s*/', $ids, -1, PREG_SPLIT_NO_EMPTY);

    $query = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->condition('status', 1)
      ->condition('type', 'hearing')
      ->condition('nid', $ids, 'IN');

    $hearingIds = $query->execute();

    $diff = array_diff($ids, $hearingIds);
    if (!empty($diff)) {
      throw new RuntimeException('Invalid ids: ' . implode(', ', $diff));
    }

    $hearings = Node::loadMultiple($ids);
    foreach ($hearings as $hearing) {
      $this->output->writeln('Hearing: ' . $hearing->id());
      $result = $this->helper->synchronizeHearingTickets($hearing);
      $this->output->writeln([
        'Result',
        json_encode($result, JSON_PRETTY_PRINT),
        str_repeat('-', 80),
      ]);
    }
  }

  /**
   * Shows information on data synchronization endpoint.
   *
   * @param int $hearingId
   *   Hearing id.
   * @param int $ticketId
   *   Ticket id.
   *
   * @command hoeringsportal:deskpro:synchronize-endpoint
   */
  public function synchronizeEndpoint(int $hearingId, int $ticketId) {
    $url = $this->helper->getTicketSynchronizationUrl();

    $headers = $this->helper->getDataSynchronizationHeaders();
    $payload = $this->helper->getTicketSynchronizationPayload($hearingId, $ticketId);

    $this->output->writeln([
      'url:',
      $url,
      '',
    ]);
    $this->output->writeln('headers:');
    foreach ($headers as $name => $value) {
      $this->output->writeln($name . ': ' . $value);
    }
    $this->output->writeln('');

    $this->output->writeln([
      'Example payload:',
      json_encode($payload, JSON_PRETTY_PRINT),
      '',
    ]);

    $curlCommand[] = 'curl';
    $curlCommand[] = $url;
    foreach ($headers as $name => $value) {
      $curlCommand[] = '--header ' . escapeshellarg($name . ': ' . $value);
    }
    $curlCommand[] = '--data ' . escapeshellarg(json_encode($payload));

    $this->output->writeln('Example curl commands:');
    $this->output->writeln([
      '',
      implode(' ', $curlCommand),
    ]);
  }

}
