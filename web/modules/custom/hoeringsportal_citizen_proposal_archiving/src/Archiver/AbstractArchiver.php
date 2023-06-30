<?php

namespace Drupal\hoeringsportal_citizen_proposal_archiving\Archiver;

use Drupal\Core\Site\Settings;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Abstract archiver for citizen proposal archiving.
 */
abstract class AbstractArchiver implements LoggerAwareInterface, LoggerInterface {
  use LoggerAwareTrait;
  use LoggerTrait;

  /**
   * Archive node.
   */
  abstract public function archive(NodeInterface $node);

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    $this->logger->log($level, $message, $context);
  }

}
