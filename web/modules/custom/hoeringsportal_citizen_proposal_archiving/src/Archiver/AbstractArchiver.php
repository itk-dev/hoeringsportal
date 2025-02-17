<?php

namespace Drupal\hoeringsportal_citizen_proposal_archiving\Archiver;

use Drupal\Core\Database\Connection;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Abstract archiver for citizen proposal archiving.
 */
abstract class AbstractArchiver implements LoggerAwareInterface, LoggerInterface {
  use LoggerAwareTrait;
  use LoggerTrait;

  private const TABLE_NAME = 'hoeringsportal_citizen_proposal_archiving';

  /**
   * Constructor.
   */
  public function __construct(
    readonly private Connection $database,
    #[Autowire(service: 'logger.channel.hoeringsportal_citizen_proposal_archiving')]
    LoggerInterface $logger,
  ) {
    $this->setLogger($logger);
  }

  /**
   * Archive node.
   */
  abstract public function archive(NodeInterface $node, string $content, string $contentType);

  /**
   * Get archival info.
   */
  abstract public function getArchivalInfo(NodeInterface $node): ?array;

  /**
   * Set archival info.
   */
  protected function addArchivalInfo(NodeInterface $node, array $data, $reset = FALSE) {
    $info = $this->loadArchivalInfo($node);
    $now = \Drupal::time()->getCurrentTime();
    if (NULL === $info) {
      $this->database->insert(self::TABLE_NAME)
        ->fields(
          ['archiver', 'node_id', 'data', 'created', 'updated'],
          [static::class, $node->id(), json_encode($data), $now, $now]
        )
        ->execute();
    }
    else {
      if (!$reset) {
        $data += $info;
      }
      $this->database->update(self::TABLE_NAME)
        ->condition('archiver', static::class)
        ->condition('node_id', $node->id())
        ->fields([
          'data' => json_encode($data),
          'updated' => $now,
        ])
        ->execute();
    }
  }

  /**
   * Load archival info.
   */
  protected function loadArchivalInfo(NodeInterface $node): ?array {
    $result = $this->database->select(self::TABLE_NAME, 't')
      ->fields('t')
      ->condition('archiver', static::class)
      ->condition('node_id', $node->id())
      ->execute()
      ->fetchObject();

    if (!$result) {
      return NULL;
    }

    $result = (array) $result;
    $result['data'] = json_decode($result['data'], TRUE);

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []): void {
    $this->logger->log($level, $message, $context);
  }

}
