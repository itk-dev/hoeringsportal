# Hoeringsportal local development

## Local setup

Create the file `web/sites/default/settings.local.php` and add:

```php
<?php
/**
 * @file
 * Local settings.
 */

/**
 * Add development service settings.
 */
if (file_exists(__DIR__ . '/services.local.yml')) {
  $settings['container_yamls'][] = __DIR__ . '/services.local.yml';
}

/**
 * Disable CSS and JS aggregation.
 */
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

/**
 * Disable caching.
 */
$settings['cache']['bins']['render'] = 'cache.backend.null';
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
$settings['cache']['bins']['page'] = 'cache.backend.null';

/**
 * Setup logging.
 */
$config['system.logging']['error_level'] = 'verbose';

/**
 * Set Hash salt value.
 */
$settings['hash_salt'] = 'GIVE_ME_STRING';

/**
 * Set trusted host pattern.
 */
$settings['trusted_host_patterns'] = [
  '^hoeringsportal\.local\.itkdev\.dk$',
];

/**
 * Set local db.
 */
$databases['default']['default'] = [
  'database' => getenv('DATABASE_DATABASE') ?: 'db',
  'username' => getenv('DATABASE_USERNAME') ?: 'db',
  'password' => getenv('DATABASE_PASSWORD') ?: 'db',
  'host' => getenv('DATABASE_HOST') ?: 'mariadb',
  'port' => getenv('DATABASE_PORT') ?: '',
  'driver' => getenv('DATABASE_DRIVER') ?: 'mysql',
  'prefix' => '',
];
```

Start docker

```sh
docker compose pull
docker compose up --detach
# Note: If you want to start pretix and the mock OIDC IdP you have to enable the "pretix" and "oidc" profiles (cf. https://docs.docker.com/compose/profiles/):
# docker compose --profile pretix --profile oidc up --detach
docker compose exec phpfpm composer install
docker compose exec phpfpm vendor/bin/drush --yes site:install --existing-config

# Get admin sign in url
docker compose exec phpfpm vendor/bin/drush --yes --uri="http://hoeringsportal.local.itkdev.dk" user:login
```

Add all fixtures

```sh name=load-fixtures
docker compose exec phpfpm vendor/bin/drush --yes pm:enable hoeringsportal_base_fixtures $(find web/modules/custom -type f -name 'hoeringsportal_*_fixtures.info.yml' -exec basename -s .info.yml {} \;)
docker compose exec phpfpm vendor/bin/drush --yes content-fixtures:load
docker compose exec phpfpm vendor/bin/drush --yes pm:uninstall content_fixtures
```

### Coding standards and code analysis

All code must follow the [Drupal coding standards](https://www.drupal.org/docs/develop/standards).

#### Coding standards

Apply and check coding standard  by running

```sh
task coding-standards:check
```

#### Code analysis

```sh
task code-analysis
```

#### Markdown

Apply and check Markdown coding standards:

```sh
task coding-standards:markdown:check
```

## About translations

Import translations by running

```sh
(cd web && ../vendor/bin/drush locale:import --type=customized --override=all da ../translations/custom-translations.da.po)
```

Export translations by running

```sh
(cd web && ../vendor/bin/drush locale:export da --types=customized > ../translations/custom-translations.da.po)
```

Open `translations/custom-translations.da.po` with the latest version of
[Poedit](https://poedit.net/) to clean up and then save the file.

See
[How to deploy drupal interface translations](https://medium.com/limoengroen/how-to-deploy-drupal-interface-translations-5653294c4af6)
for further details.

### Further local setup

[OpenIdConnect local setup](openIdConnect.md)

[Deskpro local setup Readme](../web/modules/custom/hoeringsportal_deskpro/README.md)

[Pretix local setup Readme](pretix.md#local-setup)

## Production setup

```sh
composer install --no-dev --optimize-autoloader
```

## Deskpro

See [hoeringsportal_deskpro/README.md](web/modules/custom/hoeringsportal_deskpro/README.md#test-mode).
