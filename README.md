# Høringsportal

## Configuration

### Guide for local development README

[Local development](web/documentation/localDevelopment.md)

### OpenIdConnect setup README

[OpenIdConnect setup](web/documentation/openIdConnect.md)

### Deskpro setup README

[Deskpro setup Readme](web/modules/custom/hoeringsportal_deskpro/README.md)

### Pretix README

[Pretix setup Readme](web/documentation/pretix.md)

## Database dumps

The `docker-compose` setup contains a couple of database dumps, one for Drupal
and one for pretix, to make it easy to get started. When adding new
functionality to Drupal, you may need to upgrade the database dump.

## Setting up cron

A number of `cron` jobs must be set up to make things happen automagically; see

* [Hoeringsportal data](web/modules/custom/hoeringsportal_data/README.md)
* [Hoeringsportal public meeting](web/modules/custom/hoeringsportal_public_meeting/README.md)

```sh
# Dump the database
docker-compose exec phpfpm vendor/bin/drush sql:dump --extra-dump='--skip-column-statistics' --structure-tables-list="cache,cache_*,advancedqueue,history,search_*,sessions,watchdog" --gzip --result-file=/app/.docker/drupal/dumps/drupal.sql
```
