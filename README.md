# Høringsportal

## Configuration

Check out
[`web/modules/custom/hoeringsportal_deskpro/README.md`](web/modules/custom/hoeringsportal_deskpro/README.md)
for details on configuring the Deskpro integration.

### SAML settings

Add this to `settings.local.php` and edit to match your actual setup.

```php
// IDP configuration
$config['samlauth.authentication']['idp_single_log_out_service'] = 'IDP single log out service url';
$config['samlauth.authentication']['idp_single_sign_on_service'] = 'IDP single sign on service url';
$config['samlauth.authentication']['idp_x509_certificate'] = 'IDP x509 certificate';
// Setting of this depends on your IDP
$config['samlauth.authentication']['security_request_authn_context'] = false;

// SP configuration
$config['samlauth.authentication']['sp_entity_id'] = 'SP entity id';

// Load certificate and key from certs folder.
$config['samlauth.authentication']['sp_cert_folder'] = __DIR__;
// Alternatively, set certificate and key here.
// $config['samlauth.authentication']['sp_x509_certificate'] = 'SP x509 certificate';
// $config['samlauth.authentication']['sp_private_key'] = 'SP private key';
```

## Ignored configuration

The [config_ignore module](https://www.drupal.org/project/config_ignore) is used
to ignore configuration for the [`itk_pretix`
module](https://github.com/itk-dev/itk_pretix_d8).

## Installation

A number of `cron` jobs must be set up to make things happen automagically; see
* [web/modules/custom/hoeringsportal_data/README.md](web/modules/custom/hoeringsportal_data/README.md)
* [web/modules/custom/hoeringsportal_public_meeting/README.md](web/modules/custom/hoeringsportal_public_meeting/README.md)

## Translations

Import translations by running

```sh
(cd web && ../vendor/bin/drush locale:import --type=customized --override=all da ../translations/custom-translations.da.po)
```

Export translations by running

```sh
(cd web && ../vendor/bin/drush locale:export da --types=customized > ../translations/custom-translations.da.po)
```

Open `translations/custom-translations.da.po` with the latest version of
[Poedit](https://poedit.net/) to clean up and then save the file.

See
https://medium.com/limoengroen/how-to-deploy-drupal-interface-translations-5653294c4af6
for further details.

### Built-in server

Create a database connection in settings.local.php
```php
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
  'port' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

```

Create db
```sh
composer install
./vendor/bin/drush  --yes site-install --account-name=admin --account-mail=admin@example.com --config-dir=$PWD/config/sync
```

Start server
```sh
./vendor/bin/drush runserver
```

Start server with xdebug and PHPStorm
```sh
(cd web && \
XDEBUG_CONFIG="idekey=PHPSTORM remote_enable=1 remote_mode=req remote_port=9000 remote_host=127.0.0.1 remote_connect_back=0" \
  php -S 127.0.0.1:8888 ../vendor/drush/drush/misc/d8-rs-router.php)
```

### Updating

```sh
composer install
./vendor/bin/drush --yes updatedb
./vendor/bin/drush --yes config:import
./vendor/bin/drush --yes locale:update
./vendor/bin/drush --yes cache:rebuild
```

For production you should use

```sh
composer install --no-dev --optimize-autoloader
```

## Coding standards

All code must follow the [Drupal coding standards](https://www.drupal.org/docs/develop/standards).

Check the code by running

```sh
composer check-coding-standards
```

Apply automatic coding standard fixes by running

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

## system_status module

After installation, you must uninstall and enable the
[system_status](https://www.drupal.org/project/system_status) module to get
"Your siteUUID":

```sh
(cd web \
&& ../vendor/bin/drush --yes pm:uninstall system_status \
&& ../vendor/bin/drush --yes pm:enable system_status \
&& ../vendor/bin/drush config:get system_status.settings)
```

Now, add these lines to `settings.local.php` (update `«system_status_token»`
and `«system_status_encrypt_token»` to match the values reported above):

```php
$config['system_status.settings']['system_status_token'] = '«system_status_token»';
$config['system_status.settings']['system_status_encrypt_token'] = '«system_status_encrypt_token»';
```

## `docker`

Create `.env` with the following content:

```sh
COMPOSE_PROJECT_NAME=hoeringsportal
COMPOSE_DOMAIN=hoeringsportal.local.itkdev.dk
```

Start the containers:

```sh
docker-compose pull
docker-compose up -d
```

Make sure that everything is up to date:

```sh
# Drupal
docker-compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes updatedb
docker-compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes config:import
docker-compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes locale:update
docker-compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes cache:rebuild

# pretix
docker-compose exec pretix python /pretix/src/manage.py migrate
docker-compose exec pretix python /pretix/src/manage.py compress
docker-compose exec pretix python /pretix/src/manage.py collectstatic --no-input
```

Sign in to Drupal:

```sh
docker-compose exec phpfpm vendor/bin/drush --uri=http://hoeringsportal.local.itkdev.dk/ user:login
```

Sign in to Pretix:

Go to http://pretix.hoeringsportal.local.itkdev.dk/control/ and sign in with
username `admin@localhost` and password `admin`.

### `mutagen`

```sh
brew install mutagen-io/mutagen/mutagen
mutagen project start
```

### `symfony`

Install `symfony` from https://symfony.com/download

```sh
docker-compose up -d
symfony serve
```

### Drupal

```sh
docker-compose exec phpfpm composer install
docker-compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes updatedb
docker-compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes config:import
docker-compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes locale:update
docker-compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes cache:rebuild
```

### pretix

```sh
docker-compose exec pretix python /pretix/src/manage.py migrate
docker-compose exec pretix python /pretix/src/manage.py compress
docker-compose exec pretix python /pretix/src/manage.py collectstatic --no-input
```

#### API

```sh
curl --header 'Authorization: Token v84pb9f19gv5gkn2d8vbxoih6egx2v00hpbcwzwzqoqqixt22locej5rffmou78e' \
  http://pretix.hoeringsportal.local.itkdev.dk/api/v1/organizers/
```

### Resetting pretix database

```sh
gunzip < .docker/pretix/dumps/pretix_2020-02-26.sql.gz | mysql --host=0.0.0.0 --port=$(docker-compose port pretix_database 3306 | awk -F: '{ print $2 }') --user=pretix --password=pretix pretix
```

### Database dumps

The `docker-compose` setup contains a couple of database dumps, one for Drupal
and one for pretix, to make it easy to get started. When adding new
functionality to Drupal, you may need to upgrade the database dump.

#### Drupal

```sh
# Make sure that everything is up to date
docker-compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes updatedb
docker-compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes config:import
docker-compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes locale:update
docker-compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes cache:rebuild
# Dump the database
docker-compose exec phpfpm vendor/bin/drush sql:dump --structure-tables-list="cache,cache_*,advancedqueue,history,search_*,sessions,watchdog" --gzip --result-file=/app/.docker/drupal/dumps/drupal.sql
```

#### Pretix

```sh
# Make sure that everything is up to date
docker-compose exec pretix python /pretix/src/manage.py migrate
docker-compose exec pretix python /pretix/src/manage.py compress
docker-compose exec pretix python /pretix/src/manage.py collectstatic --no-input
# Dump the database
mysqldump --host=0.0.0.0 --port=$(docker-compose port pretix_database 3306 | awk -F: '{ print $2 }') --user=pretix --password=pretix pretix | gzip > .docker/pretix/dumps/pretix.sql.gz
```
