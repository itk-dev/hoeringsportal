# Testing

## End-to-end testing

We use [Playwright](https://playwright.dev/) for end-to-end testing.

Run the following commands in a terminal:

```sh
docker network create frontend
# Run “down” only if you need to clean out your database.
# docker compose down
# Notice that we enable the "test" profile (cf. https://docs.docker.com/compose/profiles/)
docker compose --profile test pull
docker compose --profile test up --detach
docker compose exec phpfpm composer install

# Clean up
docker compose exec phpfpm vendor/bin/drush cache:rebuild
docker compose exec phpfpm vendor/bin/drush sql:query "DELETE FROM locales_target";
docker compose exec phpfpm vendor/bin/drush state:delete citizen_proposal_admin_form_values --yes
docker compose exec phpfpm vendor/bin/drush cache:rebuild

# Build theme assets
docker compose run --rm node yarn --cwd web/themes/custom/hoeringsportal install
docker compose run --rm node yarn --cwd web/themes/custom/hoeringsportal build

# Optional, but recommended for proper testing
# docker compose exec phpfpm vendor/bin/drush site:install --existing-config --yes

docker compose run --rm node yarn install
docker compose run --rm playwright npx playwright install
docker compose run --rm playwright npx playwright test
open playwright-report/index.html
```

Tests can be run in [UI Mode](https://playwright.dev/docs/test-ui-mode) using
[XQuartz](https://www.xquartz.org/):

```sh
# @see https://gist.github.com/cschiewek/246a244ba23da8b9f0e7b11a68bf3285#file-x11_docker_mac-md
# Install XQuartz: brew install xquartz
xhost + 127.0.0.1
docker compose run --rm --env DISPLAY=host.docker.internal:0 playwright npx playwright test --ui
```
