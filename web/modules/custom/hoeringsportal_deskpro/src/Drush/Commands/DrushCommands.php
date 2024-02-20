<?php

namespace Drupal\hoeringsportal_deskpro\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\hoeringsportal_deskpro\Service\HearingHelper;
use Drupal\node\Entity\Node;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands as BaseDrushCommands;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom drush commands from hoeringsportal_deskpro.
 */
final class DrushCommands extends BaseDrushCommands {
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('hoeringsportal_deskpro.helper'),
    );
  }

  /**
   * Synchronizes hearing ticket data with Deskpro.
   */
  #[CLI\Command(name: 'hoeringsportal:deskpro:synchronize-hearing-ticket')]
  #[CLI\Argument(name: 'hearingId', description: 'Hearing (node) id.')]
  #[CLI\Argument(name: 'ticketIds', description: 'Comma-separated list of ticket ids.')]
  #[CLI\Option(name: 'enqueue', description: 'Enqueue the synchronization.')]
  #[CLI\Usage(name: 'hoeringsportal:deskpro:synchronize-hearing-ticket 123 456', description: 'Refreshes Deskpro data for ticket 456 on hearing 123.')]
  public function synchronizeHearingTicket(int $hearingId, string $ticketIds, array $options = [
    'enqueue' => FALSE,
  ]) {
    // Get unique list of non-negative integers.
    $ticketIds = array_unique(
      array_filter(
        array_map('intval', preg_split('/\s*,\s*/', $ticketIds)),
        static function (int $id) {
          return $id > 0;
        }
      )
    );
    foreach ($ticketIds as $ticketId) {
      $payload = $this->helper->getTicketSynchronizationPayload($hearingId, $ticketId);

      if ($options['enqueue']) {
        $result = $this->helper->synchronizeTicket($payload);
        $this->output->writeln([
          'Enqueued',
          json_encode($result, JSON_PRETTY_PRINT),
          str_repeat('-', 80),
        ]);
      }
      else {
        $result = $this->helper->runSynchronizeTicket($payload);
        $this->output->writeln([
          'Result',
          json_encode($result, JSON_PRETTY_PRINT),
          str_repeat('-', 80),
        ]);
      }
    }
  }

  /**
   * Synchronizes hearing tickets data with Deskpro.
   */
  #[CLI\Command(name: 'hoeringsportal:deskpro:synchronize-hearing-tickets')]
  #[CLI\Argument(name: 'ids', description: 'Comma-separated list of hearing ids.')]
  #[CLI\Usage(name: 'hoeringsportal:deskpro:synchronize-hearing-tickets 42,87', description: 'Refreshes Deskpro data for hearings with ids 42 and 87.')]
  public function synchronizeHearingTickets($ids) {
    $ids = preg_split('/\s*,\s*/', $ids, -1, PREG_SPLIT_NO_EMPTY);

    $query = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->accessCheck(FALSE)
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
   */
  #[CLI\Command(name: 'hoeringsportal:deskpro:synchronize-endpoint')]
  #[CLI\Argument(name: 'hearingId', description: 'Hearing (node) id.')]
  #[CLI\Argument(name: 'ticketId', description: 'Ticket id.')]
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
