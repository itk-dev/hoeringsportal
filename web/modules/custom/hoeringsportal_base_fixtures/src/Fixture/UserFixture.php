<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\user\Entity\Role;
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
    // Create a user for each defined role.
    $roles = Role::loadMultiple();
    foreach (array_keys($roles) as $role) {
      $user = User::create([
        'name' => $role,
        'mail' => $role . '@example.com',
        'pass' => $role . '-password',
        'status' => 1,
        'roles' => [
          $role,
        ],
      ]);
      $user->save();
      $this->setReference('user:citizen_proposal_editor', $user);
    }

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
