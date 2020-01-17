<?php

/**
 * @see https://pantheon.io/docs/read-environment-config
 */

$patheon_get_app_env = function () {
  if (getenv('PANTHEON_ENVIRONMENT') === 'live') {
    return 'prod';
  }
  else {
    return getenv('PANTHEON_ENVIRONMENT');
  }
};

return [
  'APP_ENV' => $patheon_get_app_env(),
  'HOSTNAME' => getenv('PANTHEON_ENVIRONMENT') .'-'. getenv('PANTHEON_SITE_NAME') . '.pantheon.io',
  'DRUPAL_DB_NAME' => getenv('DB_NAME'),
  'DRUPAL_DB_USER' => getenv('DB_USER'),
  'DRUPAL_DB_PASS' => getenv('DB_PASSWORD'),
  'DRUPAL_DB_HOST' => getenv('DB_HOST'),
  'DRUPAL_DB_PORT' => getenv('DB_PORT'),
];
