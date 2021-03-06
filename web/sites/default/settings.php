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

// As the settings.php file is not writable during install, Drupal will refuse to install a profile that is not defined
// here.
$settings['install_profile'] = 'minimal';

// Configuration sync path.
$config_directories['sync'] = '../config/sync';

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

// Local settings. These come last so that they can override anything.
if (file_exists(__DIR__ . '/settings.local.php')) {
  include __DIR__ . '/settings.local.php';
}
