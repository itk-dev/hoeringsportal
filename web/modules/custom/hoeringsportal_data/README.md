# Høringsportal data

Data types and helpers for Høringsportalen

## Drush commands

Update `state` on hearings by running

```
drush hoeringsportal:data:hearing-state-update
```

This should be done regularly by `cron` or other similar means,
e.g. every minute

```
*/5 * * * * drush hoeringsportal:data:hearing-state-update
```


## Building assets

First, install tools and requirements:

```sh
yarn install
```

Build for development:

```
yarn encore dev --watch
```

Build for production:

```
yarn encore production
```
