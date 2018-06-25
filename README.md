# Høringsportal

## Installation

### Built-in server

```sh
composer install
./vendor/bin/drush  --yes site-install minimal --db-url='mysql://dev:password@localhost/hoeringsportal' --account-name=admin --account-mail=admin@example.com --config-dir=$PWD/config/sync
./vendor/bin/drush runserver
```
