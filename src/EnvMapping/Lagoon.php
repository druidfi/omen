<?php

$lagoon_get_app_env = function () {
    if (getenv('LAGOON_ENVIRONMENT_TYPE') === 'development') {
        return 'dev';
    }
    else if (getenv('LAGOON_ENVIRONMENT_TYPE') === 'production') {
        return 'prod';
    }
};

return [
    'APP_ENV' => $lagoon_get_app_env(),
    'HOSTNAME' => getenv('HOSTNAME'),
    'DRUPAL_DB_NAME' => getenv('MARIADB_DATABASE'),
    'DRUPAL_DB_USER' => getenv('MARIADB_USERNAME'),
    'DRUPAL_DB_PASS' => getenv('MARIADB_PASSWORD'),
    'DRUPAL_DB_HOST' => getenv('MARIADB_HOST'),
    'DRUPAL_DB_PORT' => '3306',
];
