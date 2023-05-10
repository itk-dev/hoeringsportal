<?php

namespace Drupal\hoeringsportal_base_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\user\Entity\User;

/**
 * User fixture.
 *
 * @package Drupal\hoeringsportal_base_fixtures\Fixture
 */
class UserFixture extends AbstractFixture implements FixtureGroupInterface {

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

}
