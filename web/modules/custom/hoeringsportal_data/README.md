# Høringsportal data

Data types and helpers for Høringsportalen

## GeoJSON API

### Version 2

The version 2 api supports paging and paging links are returned as [a HTTP
`link` header](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Link)
(cf. <https://datatracker.ietf.org/doc/html/rfc8288>), e.g. (line breaks added
for human readability)

```http
link: <https://127.0.0.1:8000/api/v2/geojson/hearings?page=4>; rel="self",
      <https://127.0.0.1:8000/api/v2/geojson/hearings?page=3>; rel="prev",
      <https://127.0.0.1:8000/api/v2/geojson/hearings?page=5>; rel="next"
```

`/api/v2/geojson/hearings`

Returns Hearings in descending order by creation time.

## Drush commands

Update `state` on hearings by running

```sh
drush hoeringsportal:data:hearing-state-update
```

This should be done regularly by `cron` or other similar means,
e.g. every minute

```sh
*/5 * * * * drush hoeringsportal:data:hearing-state-update
```

Delete replies on hearings by running

```sh
drush hoeringsportal:data:delete-replies
```

This should be done regularly by `cron` or other similar means,
e.g. daily at 03:00

```sh
0 3 * * * drush hoeringsportal:data:delete-replies
```

## Building assets

First, install tools and requirements:

```sh
yarn install
```

Build for development:

```sh
yarn encore dev --watch
```

Build for production:

```sh
yarn encore production
```
