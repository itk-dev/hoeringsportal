<?php

namespace Drupal\hoeringsportal_citizen_proposal_archiving;

use Drupal\node\NodeInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * Archiver for citizen proposal archiving.
 */
final class Archiver implements LoggerAwareInterface, LoggerInterface {
  use LoggerAwareTrait;
  use LoggerTrait;

  /**
   * Constructor.
   */
  public function __construct(
    LoggerInterface $logger
  ) {
    $this->setLogger($logger);
  }

  /**
   * Archive node.
   */
  public function archive(NodeInterface $node) {
    $this->debug('Archiving node @label', [
      '@label' => $node->label(),
    ]);

    $this->archiveCitizenProposal($node);

    $this->debug('Done archiving node @label', [
      '@label' => $node->label(),
    ]);
  }

  /**
   * Archive citizen proposal.
   */
  private function archiveCitizenProposal($node) {
    $this->debug(__METHOD__);
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    $this->logger->log($level, $message, $context);
  }

}
