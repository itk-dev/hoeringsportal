# Høringsportal

## Installation

### Built-in server

Create a database connection in settings.local.php
```sh
<?php
/**
 * Add development service settings.
 */
if (file_exists(__DIR__ . '/services.local.yml')) {
  $settings['container_yamls'][] = __DIR__ . '/services.local.yml';
}


/**
 * Disable CSS and JS aggregation.
 */
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;


/**
 * Set Hash salt value
 */
$settings['hash_salt'] = 'GIVE_ME_STRING';


/**
 * Set local db
 */
$databases['default']['default'] = array (
  'database' => 'hoeringsportal',
  'username' => 'dev',
  'password' => 'password',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);


/**
 * Set sync path
 */
$config_directories['sync'] = '../config/sync';
```

Create db and start server
```sh
composer install
./vendor/bin/drush  --yes site-install --account-name=admin --account-mail=admin@example.com --config-dir=$PWD/config/sync
./vendor/bin/drush runserver
```


### Updating

```sh
composer install
./vendor/bin/drush --yes config:import
./vendor/bin/drush --yes cache-rebuild
```

## Coding standards

All code must follow the [Drupal coding standards](https://www.drupal.org/docs/develop/standards).

Check the code by running

```sh
composer check-coding-standards
```

Apply automatic conding standard fixes by running

```sh
composer apply-coding-standards
```

### Drush helper commands

In Drush 9, shell aliases have gone the way of the dodo, so we need other tricks to pull data from remote sites:

First, copy `drush/sites/self.site.yml.dist` to `drush/sites/self.site.yml` and edit as needed.

Then you can pull remote data (database and files) by running

```sh
./drush/scripts/pull [stg|prod]
```

## Composer virtualenv

If you get tired of writing `./vendor/bin/drush`, you can run

```sh
source ./vendor/bin/activate
```

to add `vendor/bin` to your path. See
https://github.com/itk-dev/composer-virtualenv for details.
