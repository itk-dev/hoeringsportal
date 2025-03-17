<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\user\Entity\User;

/**
 * User fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class UserFixture extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $user = User::create([
      'name' => 'administrator',
      'mail' => 'administrator@example.com',
      'pass' => 'administrator-password',
      'status' => 1,
      'roles' => [
        'administrator',
      ],
    ]);
    $user->save();
    $this->setReference('user:administrator', $user);

    $user = User::create([
      'name' => 'citizen_proposal_editor',
      'mail' => 'citizen_proposal_editor@example.com',
      'pass' => 'citizen_proposal_editor-password',
      'status' => 1,
      'roles' => [
        'citizen_proposal_editor',
      ],
    ]);
    $user->save();
    $this->setReference('user:citizen_proposal_editor', $user);

    $user = User::create([
      'name' => 'public_meeting_editor',
      'mail' => 'public_meeting_editor@example.com',
      'pass' => 'public_meeting_editor-password',
      'status' => 1,
      'roles' => [
        'public_meeting_editor',
      ],
    ]);
    $user->save();
    $this->setReference('user:public_meeting_editor', $user);

    $user = User::create([
      'name' => 'editor',
      'mail' => 'editor@example.com',
      'pass' => 'editor-password',
      'status' => 1,
      'roles' => [
        'editor',
      ],
    ]);
    $user->save();
    $this->setReference('user:editor', $user);

    // We use the range 100..103 to prevent usernames clashing with the ones
    // used in OIDCUserFixture.
    foreach (range(100, 103) as $i) {
      $departmentNumber = ($i - 1) % TermDepartmentFixture::NUMBER_OF_TERMS + 1;
      $name = sprintf('department%d-editor', $i);
      $user = User::create([
        'name' => $name,
        'mail' => $name . '@example.com',
        'pass' => $name . '-password',
        'status' => 1,
        'roles' => [
          'editor',
        ],
        'field_department' => [
          $this->getReference(sprintf('department:Department %d', $departmentNumber))->id(),
        ],
      ]);
      $user->save();
      $this->setReference('user:' . $name, $user);
    }

    $user = User::create([
      'name' => 'user',
      'mail' => 'user@example.com',
      'pass' => 'user-password',
      'status' => 1,
      'roles' => [
        'authenticated',
      ],
    ]);
    $user->save();
    $this->setReference('user:user', $user);
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['user'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      TermDepartmentFixture::class,
    ];
  }

}
