# Pretix setup

## Ignored configuration

The [config_ignore module](https://www.drupal.org/project/config_ignore) is used
to ignore configuration for the [`itk_pretix`
module](https://github.com/itk-dev/itk_pretix_d8).

## Local setup

This project ships with a docker environment that includes a local pretix setup
for use during development.

Our pretix service must be able to call a [webhook
controller](https://github.com/itk-dev/itk_pretix/blob/main/src/Controller/PretixWebhookController.php) in our Drupal
(phpfpm) service and this requires [a little setting in
`settings.local.php`](https://github.com/itk-dev/itk_pretix/blob/main/README.md#configuration):

``` php
$settings['itk_pretix']['drupal_base_url'] = 'http://hoeringsportal.local.itkdev.dk:8080';
```

### Pretix build

Sign in to pretix:

Go to [Local pretix](http://pretix.hoeringsportal.local.itkdev.dk/control/) and
sign in with username `admin@localhost` and password `admin`.

#### Resetting pretix database

```sh name=pretix-database-load
task pretix:database-reset
```

```sh name=pretix-database-dump
task compose -- pull
task compose-up
task pretix:database-dump
```

### Drupal build

#### Connect Drupal to pretix

```sh name=pretix-configure-drupal
task drush -- config:set itk_pretix.pretixconfig pretix_url 'http://pretix.hoeringsportal.local.itkdev.dk/'
task drush -- config:set itk_pretix.pretixconfig organizer_slug 'hoeringsportal'
task drush -- config:set itk_pretix.pretixconfig api_token 'v84pb9f19gv5gkn2d8vbxoih6egx2v00hpbcwzwzqoqqixt22locej5rffmou78e'
task drush -- config:set itk_pretix.pretixconfig template_event_slugs 'template-series'
```

Go to
<http://hoeringsportal.local.itkdev.dk/admin/config/itk_pretix/pretixconfig> for
more pretix configuration.

### API

``` sh name=pretix-api-get-organizers
curl --header 'Authorization: Token v84pb9f19gv5gkn2d8vbxoih6egx2v00hpbcwzwzqoqqixt22locej5rffmou78e' \
  http://pretix.hoeringsportal.local.itkdev.dk/api/v1/organizers/
```

### Making sure everything is up to date

```sh
# Drupal setup
docker compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes deploy
docker compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes locale:update

docker compose exec phpfpm vendor/bin/drush --yes --uri=http://hoeringsportal.local.itkdev.dk/ config:set itk_pretix.pretixconfig pretix_url 'http://pretix.hoeringsportal.local.itkdev.dk/'
docker compose exec phpfpm vendor/bin/drush --yes --uri=http://hoeringsportal.local.itkdev.dk/ config:set itk_pretix.pretixconfig organizer_slug 'hoeringsportal'
docker compose exec phpfpm vendor/bin/drush --yes --uri=http://hoeringsportal.local.itkdev.dk/ config:set itk_pretix.pretixconfig api_token 'v84pb9f19gv5gkn2d8vbxoih6egx2v00hpbcwzwzqoqqixt22locej5rffmou78e'
docker compose exec phpfpm vendor/bin/drush --yes --uri=http://hoeringsportal.local.itkdev.dk/ config:set itk_pretix.pretixconfig template_event_slugs 'template-series'

docker compose exec phpfpm /app/vendor/bin/drush --root=/app/web --yes cache:rebuild
```

```sh
# Pretix setup
docker compose exec pretix python /pretix/src/manage.py migrate
docker compose exec pretix python /pretix/src/manage.py compress
docker compose exec pretix python /pretix/src/manage.py collectstatic --no-input
# Dump the database
mysqldump --host=0.0.0.0 --port=$(docker compose port pretix_database 3306 | awk -F: '{ print $2 }') --user=pretix --password=pretix pretix | gzip > .docker/pretix/dumps/pretix.sql.gz
```
