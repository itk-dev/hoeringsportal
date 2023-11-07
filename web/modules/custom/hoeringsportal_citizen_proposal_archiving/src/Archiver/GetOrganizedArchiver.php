<?php

namespace Drupal\hoeringsportal_citizen_proposal_archiving\Archiver;

use Drupal\Core\Database\Connection;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Site\Settings;
use Drupal\hoeringsportal_citizen_proposal_archiving\Exception\GetOrganizedException;
use Drupal\node\NodeInterface;
use ItkDev\GetOrganized\Client;
use ItkDev\GetOrganized\Service\Documents;
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
   * The GetOrganized documents service.
   *
   * @var \ItkDev\GetOrganized\Service\Documents
   */
  private Documents $documents;

  /**
   * Constructor.
   */
  public function __construct(
    readonly private FileSystem $fileSystem,
    Connection $database,
    LoggerInterface $logger
  ) {
    parent::__construct($database, $logger);
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
   * {@inheritdoc}
   */
  public function getArchivalInfo(NodeInterface $node): ?array {
    $info = $this->loadArchivalInfo($node);

    if (NULL === $info) {
      return NULL;
    }

    return [
      'archived_at' => $info['updated'],
      'archive_url' => $info['data']['url'] ?? NULL,
      'response' => $info['data']['response'] ?? NULL,
    ];
  }

  /**
   * Archive citizen proposal.
   */
  private function archiveCitizenProposal(NodeInterface $node, string $content, string $contentType) {
    $caseID = $node->get('field_getorganized_case_id')->getString();
    if (empty($caseID)) {
      throw new GetOrganizedException(sprintf('Invalid or missing GetOrganized case ID: %s', $caseID ?? json_encode($caseID)));
    }

    try {
      $options = $this->getOptions();
      $replacements = [
        '%nid%' => $node->id(),
      ];
      $documentName = str_replace(array_keys($replacements), $replacements, $options['document_name_template']);
      $path = $this->fileSystem->getTempDirectory() . '/' . $documentName;
      file_put_contents($path, $content);

      // Unfinalize document in order to be able to update it.
      $info = $this->getArchivalInfo($node);
      if (isset($info['response'])) {
        $this->unfinalizeDocument($info['response']);
      }

      $response = $this->addToCase($path, $caseID);
      if (empty($response)) {
        throw new GetOrganizedException(sprintf('Error archiving citizen proposal %s (%s); empty response', $node->id(), $node->label()));
      }
      $this->addArchivalInfo($node, ['response' => $response]);

      $this->finalizeDocument($response);

      $this->debug('@message', [
        '@message' => json_encode([
          'node' => $node->label(),
          'getOrganizedCaseId' => $caseID,
          'response' => $response,
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

  /**
   * Get the GetOrganized documents service.
   */
  public function getDocuments(): Documents {
    if (empty($this->documents)) {
      $options = $this->getOptions();
      $client = new Client(
        $options['api_username'],
        $options['api_password'],
        $options['api_url']
      );
      $this->documents = $client->api('documents');
    }

    return $this->documents;
  }

  /**
   * Add file to GetOrganized case.
   */
  private function addToCase(string $path, string $caseID): ?array {
    $this->logger->debug(sprintf('Add to case %s: %s', $caseID, $path));

    return $this->getDocuments()->AddToDocumentLibrary($path, $caseID, basename($path));
  }

  /**
   * Unfinalize document.
   */
  private function unfinalizeDocument(array $response): ?array {
    if (!isset($response['DocId'])) {
      $this->logger->error(sprintf('Unfinalize document; unexpected response: %s', json_encode($response)));
    }

    $docId = (int) $response['DocId'];
    $this->logger->debug(sprintf('Unfinalize document %s', $docId));

    $result = $this->getDocuments()->UnmarkFinalized([$docId]);
    if (FALSE === ($result['Success'] ?? NULL)) {
      throw new GetOrganizedException(sprintf('Error unfinalizing document %s: %s', $docId, $result['Message'] ?? '(no message)'));
    }

    return $result;
  }

  /**
   * Finalize document.
   */
  private function finalizeDocument(array $response): ?array {
    if (!isset($response['DocId'])) {
      $this->logger->error(sprintf('Finalize document; unexpected response: %s', json_encode($response)));

      return NULL;
    }

    $docId = (int) $response['DocId'];
    $this->logger->debug(sprintf('Finalize document %s', $docId));
    $response = $this->getDocuments()->Finalize((int) $response['DocId']);
    $this->logger->debug(sprintf('Finalize document %s; response: %s', $docId, json_encode($response)));

    return $response;
  }

  /**
   * Get options.
   */
  private function getOptions(): array {
    if (!isset($this->options)) {
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
          'document_name_template' => 'citizen-proposal-%nid%.pdf',
        ])
        ->resolve($settings);
    }

    return $this->options;
  }

}
