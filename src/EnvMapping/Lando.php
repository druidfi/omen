<?php

/**
 * @see https://github.com/lando/lando/blob/master/docs/config/env.md
 */

$lando_info = json_decode(getenv('LANDO_INFO'), TRUE);

return [
  'APP_ENV' => 'dev',
  'HOSTNAME' => getenv('LANDO_APP_NAME') .'.'. getenv('LANDO_DOMAIN'),
  'DRUPAL_DB_NAME' => $lando_info['database']['creds']['database'],
  'DRUPAL_DB_USER' => $lando_info['database']['creds']['user'],
  'DRUPAL_DB_PASS' => $lando_info['database']['creds']['password'],
  'DRUPAL_DB_HOST' => $lando_info['database']['internal_connection']['host'],
  'DRUPAL_DB_PORT' => $lando_info['database']['internal_connection']['port'],
  'DRUPAL_HASH_SALT' => getenv('HASH_SALT'),
];
