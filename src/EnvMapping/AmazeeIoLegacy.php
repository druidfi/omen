<?php

$amazeeio_get_app_env = function () {
    if (getenv('AMAZEEIO_SITE_ENVIRONMENT') === 'development') {
        return 'dev';
    }
    else if (getenv('AMAZEEIO_SITE_ENVIRONMENT') === 'production') {
        return 'prod';
    }
};

return [
    'APP_ENV' => $amazeeio_get_app_env(),
    'HOSTNAME' => getenv('HOSTNAME'),
    'DRUPAL_DB_DRIVER' => 'mysql',
    'DRUPAL_DB_NAME' => getenv('AMAZEEIO_SITENAME'),
    'DRUPAL_DB_USER' => getenv('AMAZEEIO_DB_USERNAME'),
    'DRUPAL_DB_PASS' => getenv('AMAZEEIO_DB_PASSWORD'),
    'DRUPAL_DB_HOST' => getenv('AMAZEEIO_DB_HOST'),
    'DRUPAL_DB_PORT' => getenv('AMAZEEIO_DB_PORT'),
    'DRUPAL_TMP_PATH' => getenv('AMAZEEIO_TMP_PATH'),
];
