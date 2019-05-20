# Høringsportal – Deskpro

## Configuration

Edit configuration on `/admin/site-setup/deskpro`.

## API

Check out `/hoeringsportal_deskpro/api/docs` for details.

## Drush commands

```
hoeringsportal:deskpro:synchronize-data
hoeringsportal:deskpro:synchronize-endpoint
```
Synchronizes hearing data with Deskpro.
Shows information on data synchronization endpoint.

Process Deskpro queue:

```sh
drush advancedqueue:queue:process hoeringsportal_deskpro
```
