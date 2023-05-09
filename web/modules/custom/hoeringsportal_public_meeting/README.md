# Høringsportal public meeting

## Drush commands

Update `state` on public meetings by running

```sh
drush hoeringsportal:public_meeting:state-update
```

This should be done regularly by `cron` or other similar means,
e.g. every minute

```sh
*/5 * * * * drush hoeringsportal:public_meeting:state-update
```
