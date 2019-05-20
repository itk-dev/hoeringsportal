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
   * Synchronizes hearing data with Deskpro.
   *
   * @param string|null $ids
   *   Comma-list of hearing ids.
   *
   * @command hoeringsportal:deskpro:synchronize-data
   * @usage hoeringsportal:deskpro:synchronize-data 42,87
   *   Refreshes Deskpro data for hearings with ids 42 and 87.
   */
  public function synchronizeData($ids) {
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
   * @param string|null $id
   *   Hearing id.
   *
   * @command hoeringsportal:deskpro:synchronize-endpoint
   */
  public function synchronizeEndpoint($id = -87) {
    $url = $this->helper->getDataSynchronizationUrl();
    $urlDelayed = $this->helper->getDataSynchronizationUrl(TRUE);

    $headers = $this->helper->getDataSynchronizationHeaders();
    $payload = $this->helper->getDataSynchronizationPayload($id);

    $this->output->writeln([
      'url:',
      $url,
      '',
      'url (delayed):',
      $urlDelayed,
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

    array_splice($curlCommand, 1, 1, $urlDelayed);
    $this->output->writeln([
      '',
      implode(' ', $curlCommand),
    ]);
  }

  /**
   * Debug stuff.
   *
   * @command hoeringsportal:deskpro:debug
   */
  public function debug() {
    // $service = \Drupal::service('hoeringsportal_deskpro.data_syncronizer');
    //
    // $node = \Drupal\node\Entity\Node::load(8);
    // $service->addToQueue($node);
    // $service->addToQueue($node);
    // $service->addToQueue($node);
    // $node = \Drupal\node\Entity\Node::load(9);
    // $service->addToQueue($node);
    //
    // $service->processQueue();
    //
    // return;
    $node = Node::load(8);
    $data = [
      'name' => 'test',
      'email' => 'test@example.com',
      'address' => 'Dokk1',
      'address_secret' => TRUE,
      'representation' => 35,
      'subject' => 'Test ' . date('c'),
      'message' => 'Test ' . date('c'),
    ];

    $files = [2, 17];

    /** @var \Drupal\hoeringsportal_deskpro\Service\HearingHelper $helper */
    $helper = \Drupal::service('hoeringsportal_deskpro.helper');
    $result = $helper->createHearingTicket($node, $data, $files);

    var_export($result);
  }

}
