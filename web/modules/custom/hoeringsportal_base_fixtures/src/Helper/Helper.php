<?php

namespace Drupal\hoeringsportal_base_fixtures\Helper;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;

/**
 * A helper class for the module.
 */
class Helper {

  /**
   * Constructor.
   */
  public function __construct(
    protected readonly ExtensionPathResolver $pathResolver,
    protected readonly FileRepositoryInterface $fileRepo,
    protected readonly FileSystemInterface $fileSystem,
    protected readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
  }

  /**
   * Add images to filesystem.
   */
  public function createImagesFromAssets(): array {
    $images = [];
    $image_source_path = $this->pathResolver->getPath('module', 'hoeringsportal_base_fixtures') . '/assets/images';
    $image_target_path = 'public://fixtures/assets/images';
    $this->fileSystem->prepareDirectory($image_target_path, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    // Loop over .jpg images to add them properly to the file system.
    foreach (glob($image_source_path . '/*.jpg') as $image) {
      $destination = $this->fileSystem->copy($image, $image_target_path . '/' . basename($image), FileExists::Replace);
      $images[] = $destination;
    }

    return $images;
  }

  /**
   * Add documents to filesystem.
   */
  public function createDocumentsFromAssets(): array {
    $documents = [];
    $document_source_path = $this->pathResolver->getPath('module', 'hoeringsportal_base_fixtures') . '/assets/documents';
    $document_target_path = 'public://fixtures/assets/documents';
    $this->fileSystem->prepareDirectory($document_target_path, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    // Loop over documents to add them properly to the file system.
    foreach (glob($document_source_path . '/*.pdf') as $document) {
      $destination = $this->fileSystem->copy($document, $document_target_path . '/' . basename($document), FileExists::Replace);
      $documents[] = $destination;
    }

    foreach (glob($document_source_path . '/*.docx') as $document) {
      $destination = $this->fileSystem->copy($document, $document_target_path . '/' . basename($document), FileExists::Replace);
      $documents[] = $destination;
    }

    return $documents;
  }

  /**
   * Get text from assets/texts folder.
   *
   * @param string $filename
   *   The name of the file in assets/texts folder.
   *
   * @return string|null
   *   The contents of the file.
   */
  public function getText(string $filename): ?string {
    $texts_source_path = $this->pathResolver->getPath('module', 'hoeringsportal_base_fixtures') . '/assets/texts';
    return file_get_contents($texts_source_path . '/' . $filename) ?? NULL;
  }

}
