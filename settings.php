<?php

// Use druidfi/omen to auto-configure Drupal
//
// You can setup project specific configuration in this directory:
//
// ENV.settings.php and ENV.services.yml
// and
// local.settings.php and local.services.yml. Note: local.* should not be added to version control.
//
// These files are loaded automatically if found.
//
extract((new Druidfi\Omen\Reader(__DIR__))->getConfiguration());

// Here you can still override things

/**
 * Only in Wodby environment. @see https://wodby.com/docs/1.0/stacks/drupal/#overriding-settings-from-wodbysettingsphp
 * Can be removed if not using Wodby.
 */

if (isset($_SERVER['WODBY_APP_NAME'])) {
  // The include won't be added automatically if it's already there.
  include '/var/www/conf/wodby.settings.php';

  // Override setting from wodby.settings.php.
  $config_directories[CONFIG_SYNC_DIRECTORY] = '../conf/cmi';
}
