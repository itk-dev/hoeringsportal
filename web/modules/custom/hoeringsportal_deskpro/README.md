# Høringsportal – Deskpro

## Configuration

Edit configuration on `/admin/site-setup/deskpro`.

## API

Check out `/hoeringsportal_deskpro/api/docs` for details.

## Test mode

During testing and development, you can make this module run in test mode and
not call an actual Deskpro API. Enable test mode in `settings.local.php`:

```php
# settings.local.php
$settings['hoeringsportal_deskpro']['test_mode'] = TRUE;
```

and inject a little Deskpro configuration (cf. `/admin/site-setup/deskpro`):

``` shell name=deskpro-test-setup
docker compose exec phpfpm vendor/bin/drush sql:query "INSERT INTO key_value(collection, name, value) VALUES ('hoeringsportal_deskpro.config', 'deskpro_available_department_ids', '[1]') ON DUPLICATE KEY UPDATE value = '[1]'\G"
# Check that setting is as expected.
docker compose exec phpfpm vendor/bin/drush sql:query "SELECT * FROM key_value WHERE collection = 'hoeringsportal_deskpro.config' AND name = 'deskpro_available_department_ids'\G"
```

When in test mode, the Deskpro service will return data defined in [YAML
files](https://en.wikipedia.org/wiki/YAML) in the
[`src/Service/mock`](src/Service/mock) folder. (The `1` in the SQL incantations
above match `id: 1` in `src/Service/mock/ticket_departments.yaml`).

## Drush commands

```sh
hoeringsportal:deskpro:synchronize-endpoint
hoeringsportal:deskpro:synchronize-hearing-ticket
hoeringsportal:deskpro:synchronize-hearing-tickets
```

Process Deskpro queue:

```sh
drush advancedqueue:queue:process hoeringsportal_deskpro
```

## Building assets

```sh
docker compose run --rm node yarn --cwd web/modules/custom/hoeringsportal_deskpro install
docker compose run --rm node yarn --cwd web/modules/custom/hoeringsportal_deskpro build
```

During development you may want to watch for file changes and rebuild
when needed:

```sh
docker compose run --rm node yarn --cwd web/modules/custom/hoeringsportal_deskpro watch
```

## Coding standards

```sh
docker compose run --rm node yarn --cwd web/modules/custom/hoeringsportal_deskpro install
docker compose run --rm node yarn --cwd web/modules/custom/hoeringsportal_deskpro coding-standards-check
```

```sh
docker compose run --rm node yarn --cwd web/modules/custom/hoeringsportal_deskpro coding-standards-apply
```
