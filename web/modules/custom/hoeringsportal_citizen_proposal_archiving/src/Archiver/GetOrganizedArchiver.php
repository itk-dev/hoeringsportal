<?php

namespace Drupal\hoeringsportal_citizen_proposal_archiving\Archiver;

use Drupal\Core\Site\Settings;
use Drupal\hoeringsportal_citizen_proposal_archiving\Exception\GetOrganizedException;
use Drupal\hoeringsportal_citizen_proposal_archiving\Exception\RuntimeException;
use Drupal\node\NodeInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * GetOrgnized archiver for citizen proposal archiving.
 */
final class GetOrganizedArchiver extends AbstractArchiver {
  /**
   * The options.
   */
  private array $options;

  /**
   * Constructor.
   */
  public function __construct(
    LoggerInterface $logger
  ) {
    $this->setLogger($logger);
    $settings = Settings::get('hoeringsportal_citizen_proposal_archiving')['archiver']['get_organized'] ?? [];
    if (!is_array($settings)) {
      $settings = [];
    }
    $this->options = (new OptionsResolver())
      ->setRequired('api_url')
      ->setRequired('api_username')
      ->setRequired('api_password')
      ->setDefaults([
        'admin_url' => NULL,
      ])
      ->resolve($settings);
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
  private function archiveCitizenProposal(NodeInterface $node) {
    $caseID = $node->get('field_getorganized_case_id')->getString();
    if (empty($caseID)) {
      throw new GetOrganizedException(sprintf('Invalid or missing GetOrganized case ID: %s', $caseID));
    }

    $this->debug('@message', [
      '@message' => json_encode([
        'node' => $node->label(),
        'getOrganizedCaseId' => $caseID,
        'options' => $this->options,
      ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    $this->logger->log($level, $message, $context);
  }

}
