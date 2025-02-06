<?php

namespace Drupal\hoeringsportal_test_delta_sync_fixtures\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\user\Entity\User;

/**
 * OpenId Connect user fixtures.
 *
 * @package Drupal\hoeringsportal_test_delta_sync_fixtures\Fixture
 */
class OIDCUserFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * {@inheritdoc}
   */
  public function load() {

    $user = User::create([
      'name' => 'department3-editor',
      'uid' => 202,
      'mail' => 'department3-editor@example.com',
      'pass' => 'department3-editor',
      'status' => 1,
      'roles' => [
        'authenticated',
      ],
    ]);
    $user->save();
    $this->setReference('user:authenticated', $user);
    $user = User::create([
      'name' => 'department2-editor',
      'uid' => 201,
      'mail' => 'department2-editor@example.com',
      'pass' => 'department2-editor',
      'status' => 1,
      'roles' => [
        'authenticated',
      ],
    ]);
    $user->save();
    $this->setReference('user:authenticated', $user);
    $user = User::create([
      'name' => 'department1-admin',
      'uid' => 200,
      'mail' => 'department1-admin@example.com',
      'pass' => 'department1-admin',
      'status' => 1,
      'roles' => [
        'authenticated',
      ],
    ]);
    $user->save();
    $this->setReference('user:authenticated', $user);

    $connection = \Drupal::database();
    $values = [
      [
        'uid' => 200,
        'provider' => 'openid_connect.generic',
        'authname' => 200,
      ],
      [
        'uid' => 201,
        'provider' => 'openid_connect.generic',
        'authname' => 201,
      ],
      [
        'uid' => 202,
        'provider' => 'openid_connect.generic',
        'authname' => 202,
      ],
    ];
    $query = $connection->insert('authmap')->fields(['uid', 'provider', 'authname']);
    foreach ($values as $record) {
      $query->values($record);
    }
    $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['user'];
  }

}
