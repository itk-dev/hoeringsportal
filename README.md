# Høringsportal

## Installation

### Built-in server

```sh
composer install
./vendor/bin/drush  --yes site-install minimal --db-url='mysql://dev:password@localhost/hoeringsportal' --account-name=admin --account-mail=admin@example.com --config-dir=$PWD/config/sync
./vendor/bin/drush runserver
```

### Drush helper commands

In Drush 9, shell aliases have gone the way of the dodo, so we need other tricks to pull data from remote sites:

First, copy `drush/sites/self.site.yml.dist` to `drush/sites/self.site.yml` and edit as needed.

Then you can pull remote data (database and files) by running

```sh
./drush/scripts/pull [stg|prod]
```
