<?php

namespace Drupal\hoeringsportal_project_fixtures\Fixture;

use Drupal\block_content\Entity\BlockContent;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;

/**
 * Block content fixture.
 *
 * @package Drupal\hoeringsportal_project_fixtures\Fixture
 */
class BlockContentFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity = BlockContent
      ::create([
        'type' => 'aside_contact_info',
      ])
        ->setInfo('The main address')
        ->set('field_department', 'Redundancy Department')
        ->set('field_title', 'Department of Redundancy Department')
        ->set('field_address', 'Department
Street No.
1234
')
        ->set('field_phone_number', 1234567890)
        ->set('field_email', 'ddepartment@example.com');

    $entity->save();
    $this->addReference('block_content:aside_contact_info:the_main_address', $entity);

    $entity = BlockContent
      ::create([
        'type' => 'aside_contact_info',
      ])
        ->setInfo('Another address')
        ->set('field_department', 'The Department')
        ->set('field_title', 'Title of the Department')
        ->set('field_address', 'Street 87')
        ->set('field_phone_number', 2234455)
        ->set('field_email', 'another@example.com');

    $entity->save();
    $this->addReference('block_content:aside_contact_info:another_address', $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['block', 'project'];
  }

}
