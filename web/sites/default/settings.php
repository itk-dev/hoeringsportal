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

// Local settings. These come last so that they can override anything.
if (file_exists(__DIR__ . '/settings.local.php')) {
  include __DIR__ . '/settings.local.php';
}
