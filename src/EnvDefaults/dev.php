<?php

// Skip file system permissions hardening.
$settings['skip_permissions_hardening'] = TRUE;

// Show all error messages on the site.
$config['system.logging']['error_level'] = 'all';

// Expiration of cached pages to 0.
$config['system.performance']['cache']['page']['max_age'] = 0;

// Aggregate CSS files off.
$config['system.performance']['css']['preprocess'] = 0;

// Aggregate JavaScript files off.
$config['system.performance']['js']['preprocess'] = 0;

return [
  'config' => $config,
  'settings' => $settings,
];
