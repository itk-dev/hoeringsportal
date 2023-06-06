# Testing

## Cypress tests

Run the following commands in a terminal:

```sh
docker network create frontend
# Run “down” only if you need to clean out your database.
# docker compose down
docker compose up --detach
docker compose exec phpfpm composer install
# Build theme assets
docker compose run --rm node yarn --cwd web/themes/custom/hoeringsportal install
docker compose run --rm node yarn --cwd web/themes/custom/hoeringsportal build
docker compose run --rm node yarn install
docker compose run --rm cypress run
```

Run interactively using [XQuartz](https://www.xquartz.org/):

```sh
# @see https://docs.itkdev.dk/docs/test/cypress/getting_started
# @see https://gist.github.com/cschiewek/246a244ba23da8b9f0e7b11a68bf3285#file-x11_docker_mac-md
# Install XQuartz: brew install xquartz
# Xquartz &
# Calling `xhost` will (apparently) start Xquartz
xhost + 127.0.0.1
docker compose run --rm --env DISPLAY=host.docker.internal:0 cypress open --project .
```

# Playwright

```sh
docker compose run --rm playwright npx playwright test
open playwright-report/index.html
```

```sh
xhost + 127.0.0.1
docker compose run --rm --env DISPLAY=host.docker.internal:0 playwright npx playwright test --ui
```
