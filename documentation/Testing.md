# Testing

## Cypress tests

Run the following command in a terminal:

```sh
docker network create frontend
docker compose up --detach
docker compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
docker compose exec phpfpm bin/console hautelook:fixtures:load --no-bundles --purge-with-truncate --no-interaction
docker compose run --rm node yarn install
docker compose run --rm cypress run
```

```sh
# @see https://docs.itkdev.dk/docs/test/cypress/getting_started
# @see https://gist.github.com/cschiewek/246a244ba23da8b9f0e7b11a68bf3285#file-x11_docker_mac-md
# Xquartz &
# Calling `xhost` will (apparently) start Xquartz
xhost + 127.0.0.1
docker compose run --rm --env DISPLAY=host.docker.internal:0 cypress open --project .
```
