<?php

namespace Drupal\hoeringsportal_citizen_proposal_archiving\Archiver;

use Drupal\Core\File\FileSystem;
use Drupal\Core\Site\Settings;
use Drupal\hoeringsportal_citizen_proposal_archiving\Exception\GetOrganizedException;
use Drupal\node\NodeInterface;
use ItkDev\GetOrganized\Client;
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
    readonly private FileSystem $fileSystem,
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
  public function archive(NodeInterface $node, string $content, string $contentType) {
    $this->debug('Archiving node @label', [
      '@label' => $node->label(),
    ]);

    $this->archiveCitizenProposal($node, $content, $contentType);

    $this->debug('Done archiving node @label', [
      '@label' => $node->label(),
    ]);
  }

  /**
   * Archive citizen proposal.
   */
  private function archiveCitizenProposal(NodeInterface $node, string $content, string $contentType) {
    $caseID = $node->get('field_getorganized_case_id')->getString();
    if (empty($caseID)) {
      throw new GetOrganizedException(sprintf('Invalid or missing GetOrganized case ID: %s', $caseID));
    }

    try {
      $client = new Client($this->options['api_username'], $this->options['api_password'], $this->options['api_url']);
      /** @var \ItkDev\GetOrganized\Service\Documents $documents */
      $documents = $client->api('documents');
      $path = $this->fileSystem->getTempDirectory() . '/citizen-proposal-' . $node->id() . '.pdf';
      file_put_contents($path, $content);
      $result = $documents->AddToDocumentLibrary($path, $caseID);

      if (empty($result)) {
        throw new GetOrganizedException(sprintf('Error archiving citizen proposal %s (%s)', $node->id(), $node->label()));
      }

      $this->debug('@message', [
        '@message' => json_encode([
          'node' => $node->label(),
          'getOrganizedCaseId' => $caseID,
          'result' => $result,
          'options' => $this->options,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
      ]);
    }
    catch (\Exception $exception) {
      throw $exception;
    } finally {
      if (file_exists($path)) {
        unlink($path);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    $this->logger->log($level, $message, $context);
  }

}
