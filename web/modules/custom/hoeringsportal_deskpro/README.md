# Høringsportal – Deskpro

## Configuration

Edit configuration on `/admin/site-setup/deskpro`.

## API

Check out `/hoeringsportal_deskpro/api/docs` for details.

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

During development you may want to watch for file changes and rebuild when needed:

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
