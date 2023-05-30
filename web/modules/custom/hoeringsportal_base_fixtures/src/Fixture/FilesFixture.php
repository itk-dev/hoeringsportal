<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\file\Entity\File;
use Drupal\hoeringsportal_base_fixtures\Helper\Helper;

/**
 * File fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class FilesFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * The fixtures helper service.
   *
   * @var \Drupal\hoeringsportal_base_fixtures\Helper\Helper
   */
  protected Helper $helper;

  /**
   * Constructor.
   */
  public function __construct(Helper $helper) {
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function load() {
    $imageFiles = $this->helper->createImagesFromAssets();
    $documentFiles = $this->helper->createDocumentsFromAssets();
    foreach (array_merge($imageFiles, $documentFiles) as $publicFilePath) {
      $file = File::create([
        'filename' => basename($publicFilePath),
        'uri' => $publicFilePath,
        'status' => 1,
        'uid' => 1,
      ]);
      $file->save();
      $this->addReference('file:' . basename($publicFilePath), $file);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['images'];
  }

}
