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
