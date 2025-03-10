# Høringsportal

## Configuration

### Guides


1. [Local development](documentation/localDevelopment.md)
2. [OpenIdConnect setup](documentation/openIdConnect.md)
3. [Deskpro setup](web/modules/custom/hoeringsportal_deskpro/README.md)
4. [Pretix setup](documentation/pretix.md)
5. [Custom Høringsportalen theme](web/themes/custom/hoeringsportal/README.md)
6. [Testing](documentation/Testing.md)

## Database dumps

The `docker compose` setup contains a couple of database dumps, one for Drupal
and one for pretix (see [Pretix setup](documentation/pretix.md) for details), to
make it easy to get started. When upgrading or adding new functionality to
Drupal, you may need to upgrade the database dump:

```sh
task database-dump
```

## Setting up cron

A number of `cron` jobs must be set up to make things happen automagically; see

* [Hoeringsportal data](web/modules/custom/hoeringsportal_data/README.md)
* [Hoeringsportal public meeting](web/modules/custom/hoeringsportal_public_meeting/README.md)

## Search

``` sh name=search-reindex
task drush -- search-api:reset-tracker
task drush -- search-api:rebuild-tracker
task drush -- search-api:index
```
