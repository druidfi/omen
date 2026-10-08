<?php

namespace Druidfi\Omen\System;

class Tugboat extends AbstractSystem
{
  /**
   * @see https://docs.tugboat.qa/starter-configs/tutorials/drupal-9/
   */
  public function getEnvs(): array
  {
    return [
      'APP_ENV' => 'prod',
      'DRUPAL_DB_DRIVER' => 'mysql',
      'DRUPAL_DB_NAME' => 'tugboat',
      'DRUPAL_DB_USER' => 'tugboat',
      'DRUPAL_DB_PASS' => 'tugboat',
      'DRUPAL_DB_HOST' => 'mysql',
      'DRUPAL_DB_PORT' => '3306',
      'DRUPAL_HASH_SALT' => hash('sha256', getenv('TUGBOAT_REPO_ID')),
      'DRUPAL_ROUTES' => getenv('TUGBOAT_DEFAULT_SERVICE_URL'),
    ];
  }

  public function getEjectedCode(): string
  {
    return <<<'PHP'
// Tugboat previews always run in "prod" mode under Omen, regardless of APP_ENV.
$app_env = 'prod';

$databases['default']['default'] = [
  'driver' => 'mysql',
  'database' => 'tugboat',
  'username' => 'tugboat',
  'password' => 'tugboat',
  'host' => 'mysql',
  'port' => '3306',
  'prefix' => '',
  'init_commands' => [
    'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
  ],
];

$settings['hash_salt'] = hash('sha256', getenv('TUGBOAT_REPO_ID'));

$routes = array_values(array_filter(array_unique(explode(',', (string) getenv('TUGBOAT_DEFAULT_SERVICE_URL')))));
PHP;
  }
}
