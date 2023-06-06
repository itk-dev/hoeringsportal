# Høringsportal

## Configuration

### Guides

1. [Local development](documentation/localDevelopment.md)
2. [OpenIdConnect setup](documentation/openIdConnect.md)
3. [Deskpro setup](web/modules/custom/hoeringsportal_deskpro/README.md)
4. [Pretix setup](documentation/pretix.md)
5. [Custom Høringsportalen theme](web/themes/custom/hoeringsportal/README.md)

## Database dumps

The `docker-compose` setup contains a couple of database dumps, one for Drupal
and one for pretix, to make it easy to get started. When adding new
functionality to Drupal, you may need to upgrade the database dump.

```sh
# Dump the database
docker-compose exec phpfpm vendor/bin/drush sql:dump --extra-dump='--skip-column-statistics' --structure-tables-list="cache,cache_*,advancedqueue,history,search_*,sessions,watchdog" --gzip --result-file=/app/.docker/drupal/dumps/drupal.sql
```

## Setting up cron

A number of `cron` jobs must be set up to make things happen automagically; see

* [Hoeringsportal data](web/modules/custom/hoeringsportal_data/README.md)
* [Hoeringsportal public meeting](web/modules/custom/hoeringsportal_public_meeting/README.md)
