<?php

namespace Drupal\hoeringsportal_base_fixtures\Helper;

use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepository;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * A helper class for the module.
 */
class Helper {

  /**
   * The ExtensionPathResolver service.
   *
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected ExtensionPathResolver $pathResolver;

  /**
   * The FileRepository service.
   *
   * @var \Drupal\file\FileRepository
   */
  protected FileRepository $fileRepo;

  /**
   * The FileSystem service.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected FileSystem $fileSystem;

  /**
   * The fixtures helper service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(ExtensionPathResolver $pathResolver, FileRepository $fileRepo, FileSystem $fileSystem, EntityTypeManagerInterface $entityTypeManager) {
    $this->pathResolver = $pathResolver;
    $this->fileRepo = $fileRepo;
    $this->fileSystem = $fileSystem;
    $this->entityTypeManager = $entityTypeManager;
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
    foreach (glob($image_source_path . '/*.{jpg}', GLOB_BRACE) as $image) {
      $destination = $this->fileSystem->copy($image, $image_target_path . '/' . basename($image), FileSystemInterface::EXISTS_REPLACE);
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
    foreach (glob($document_source_path . '/*.{pdf,docx}', GLOB_BRACE) as $document) {
      $destination = $this->fileSystem->copy($document, $document_target_path . '/' . basename($document), FileSystemInterface::EXISTS_REPLACE);
      $documents[] = $destination;
    }

    return $documents;
  }

  /**
   * Get all landing pages.
   */
  public function getLandingPages(): array {
    // Get the nodes.
    return $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties(['type' => 'landing_page']);
  }

}