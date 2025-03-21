<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\media\Entity\Media;

/**
 * Page fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class MediaFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    // Documents.
    $entity = Media::create([
      'name' => 'Document1',
      'bundle' => 'document',
      'field_itk_media_mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'field_itk_media_file_upload' => ['target_id' => $this->getReference('file:1.docx')->id()],
      'field_itk_media_size' => '6000',
      'field_itk_media_tag' => ['target_id' => $this->getReference('media_library:Fil:BA')->id()],
    ]);
    $entity->save();
    $this->addReference('media:Document1', $entity);

    $entity = Media::create([
      'name' => 'Pdf1',
      'bundle' => 'document',
      'field_itk_media_mime_type' => 'application/pdf',
      'field_itk_media_file_upload' => ['target_id' => $this->getReference('file:1.pdf')->id()],
      'field_itk_media_size' => '12000',
      'field_itk_media_tag' => ['target_id' => $this->getReference('media_library:Fil:MKB')->id()],
    ]);
    $entity->save();
    $this->addReference('media:Pdf1', $entity);

    // Images.
    $entity = Media::create([
      'name' => 'Large1',
      'bundle' => 'image',
      'field_itk_media_mime_type' => 'image/jpeg',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:large1.jpg')->id()],
      'field_itk_media_tag' => ['target_id' => $this->getReference('media_library:Billede')->id()],
      'field_itk_media_height' => '2250',
      'field_itk_media_width' => '4000',
    ]);
    $entity->save();
    $this->addReference('media:Large1', $entity);

    $entity = Media::create([
      'name' => 'Large2',
      'bundle' => 'image',
      'field_itk_media_mime_type' => 'image/jpeg',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:large2.jpg')->id()],
      'field_itk_media_tag' => ['target_id' => $this->getReference('media_library:Billede')->id()],
      'field_itk_media_height' => '1503',
      'field_itk_media_width' => '2880',
    ]);
    $entity->save();
    $this->addReference('media:Large2', $entity);

    $entity = Media::create([
      'name' => 'Large3',
      'bundle' => 'image',
      'field_itk_media_mime_type' => 'image/jpeg',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:large3.jpg')->id()],
      'field_itk_media_tag' => ['target_id' => $this->getReference('media_library:Billede')->id()],
      'field_itk_media_height' => '3701',
      'field_itk_media_width' => '5550',
    ]);
    $entity->save();
    $this->addReference('media:Large3', $entity);

    $entity = Media::create([
      'name' => 'Map1',
      'bundle' => 'image',
      'field_itk_media_mime_type' => 'image/jpeg',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:map1.jpg')->id()],
      'field_itk_media_tag' => ['target_id' => $this->getReference('media_library:Billede:MTM')->id()],
      'field_itk_media_height' => '2480',
      'field_itk_media_width' => '1754',
    ]);
    $entity->save();
    $this->addReference('media:Map1', $entity);

    $entity = Media::create([
      'name' => 'Map2',
      'bundle' => 'image',
      'field_itk_media_mime_type' => 'image/jpeg',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:map2.jpg')->id()],
      'field_itk_media_tag' => ['target_id' => $this->getReference('media_library:Billede:MTM')->id()],
      'field_itk_media_height' => '1030',
      'field_itk_media_width' => '1500',
    ]);
    $entity->save();
    $this->addReference('media:Map2', $entity);

    $entity = Media::create([
      'name' => 'Map3',
      'bundle' => 'image',
      'field_itk_media_mime_type' => 'image/jpeg',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:map3.jpg')->id()],
      'field_itk_media_tag' => ['target_id' => $this->getReference('media_library:Billede:MTM')->id()],
      'field_itk_media_height' => '1198',
      'field_itk_media_width' => '1500',
    ]);
    $entity->save();
    $this->addReference('media:Map3', $entity);

    $entity = Media::create([
      'name' => 'Map4',
      'bundle' => 'image',
      'field_itk_media_mime_type' => 'image/jpeg',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:map4.jpg')->id()],
      'field_itk_media_tag' => ['target_id' => $this->getReference('media_library:Billede:MTM')->id()],
      'field_itk_media_height' => '873',
      'field_itk_media_width' => '1440',
    ]);
    $entity->save();
    $this->addReference('media:Map4', $entity);

    $entity = Media::create([
      'name' => 'Map5',
      'bundle' => 'image',
      'field_itk_media_mime_type' => 'image/jpeg',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:map5.jpg')->id()],
      'field_itk_media_tag' => ['target_id' => $this->getReference('media_library:Billede:MTM')->id()],
      'field_itk_media_height' => '1060',
      'field_itk_media_width' => '1500',
    ]);
    $entity = Media::create([
      'name' => 'Medium1',
      'bundle' => 'image',
      'field_itk_media_mime_type' => 'image/jpeg',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:medium1.jpg')->id()],
      'field_itk_media_tag' => ['target_id' => $this->getReference('media_library:Billede:BA')->id()],
      'field_itk_media_height' => '2160',
      'field_itk_media_width' => '3240',
    ]);
    $entity->save();
    $this->addReference('media:Medium1', $entity);

    $entity = Media::create([
      'name' => 'Medium2',
      'bundle' => 'image',
      'field_itk_media_mime_type' => 'image/jpeg',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:medium2.jpg')->id()],
      'field_itk_media_tag' => ['target_id' => $this->getReference('media_library:Billede:MKB')->id()],
      'field_itk_media_height' => '4096',
      'field_itk_media_width' => '4096',
    ]);
    $entity->save();
    $this->addReference('media:Medium2', $entity);

    $entity = Media::create([
      'name' => 'Medium3',
      'bundle' => 'image',
      'field_itk_media_mime_type' => 'image/jpeg',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:medium3.jpg')->id()],
      'field_itk_media_tag' => ['target_id' => $this->getReference('media_library:Billede:MKB')->id()],
      'field_itk_media_height' => '3024',
      'field_itk_media_width' => '4032',
    ]);
    $entity->save();
    $this->addReference('media:Medium3', $entity);

    $entity = Media::create([
      'name' => 'building-user-solid',
      'bundle' => 'icon',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:building-user-solid.svg')->id()],
    ]);
    $entity->save();
    $this->addReference('media:building-user-solid', $entity);

    $entity = Media::create([
      'name' => 'calendar-days-solid',
      'bundle' => 'icon',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:calendar-days-solid.svg')->id()],
    ]);
    $entity->save();
    $this->addReference('media:calendar-days-solid', $entity);

    $entity = Media::create([
      'name' => 'comments-solid',
      'bundle' => 'icon',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:comments-solid.svg')->id()],
    ]);
    $entity->save();
    $this->addReference('media:comments-solid', $entity);

    $entity = Media::create([
      'name' => 'folder-open-solid',
      'bundle' => 'icon',
      'field_itk_media_image_upload' => ['target_id' => $this->getReference('file:folder-open-solid.svg')->id()],
    ]);
    $entity->save();
    $this->addReference('media:folder-open-solid', $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      FilesFixture::class,
      TermMediaLibraryFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['media'];
  }

}
