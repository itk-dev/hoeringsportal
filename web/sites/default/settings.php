<?php
/**
 * @file
 * Default settings.php file for ITK Drupal8 projects.
 * !!! This file should should never contain sensitive information as it is added to Github. !!!
 */

// Default Drupal 8 settings.
//
// These are already explained with detailed comments in Drupal's
// default.settings.php file.
// See https://api.drupal.org/api/drupal/sites!default!default.settings.php/8

$databases = [];
$config_directories = [];
$settings['update_free_access'] = FALSE;
$settings['container_yamls'][] = __DIR__ . '/services.yml';
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

// Configuration sync path.
$settings['config_sync_directory'] = '../config/sync';

// Stop Drupal from removing write permission on settings.php.
$settings['skip_permissions_hardening'] = TRUE;

$settings['file_private_path'] = DRUPAL_ROOT . '/../private-files';

// Deskpro configuration.
$settings['hoeringsportal_deskpro.deskpro'] = [
  // Deskpro API base url, e.g. 'https://demo.deskpro.com'
  'deskpro_url' => null,
  // See https://manuals.deskpro.com/html/developer-apps/api/managing-api-keys.html
  'api_code_key' => null,
  // Deskpro field id of the "hearing" custom field.
  'hearing_field_id' => null,
];

$databases['default']['default'] = [
  'database' => getenv('DATABASE_DATABASE') ?: 'db',
  'username' => getenv('DATABASE_USERNAME') ?: 'db',
  'password' => getenv('DATABASE_PASSWORD') ?: 'db',
  'host' => getenv('DATABASE_HOST') ?: 'mariadb',
  'port' => getenv('DATABASE_PORT') ?: '',
  'driver' => getenv('DATABASE_DRIVER') ?: 'mysql',
  'prefix' => '',
];

$settings['config_sync_directory'] = '../config/sync';

// Exclude development modules from configuration synchronization
// https://www.drupal.org/node/3079028
$settings['config_exclude_modules'] = [
  'masquerade',
  'devel', 'tracer', 'webprofiler',
];

// See https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Template%21TwigSandboxPolicy.php/11.x
$settings['twig_sandbox_allowed_methods'] = [
  // Defaults (cf. Drupal\Core\Template\TwigSandboxPolicy::__construct()).
  'id',
  'label',
  'bundle',
  'get',
  'loadProjectTracks',
  '__toString',
  'toString',
  // Additions
  // Allow calling `entity.toUrl`
  'toUrl',
];

// Local settings. These come last so that they can override anything.
if (file_exists(__DIR__ . '/settings.local.php')) {
  include __DIR__ . '/settings.local.php';
}
