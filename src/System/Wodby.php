<?php

namespace Druidfi\Omen\System;

class Wodby extends AbstractSystem
{
  protected string $env_name = 'WODBY_INSTANCE_TYPE';

  /**
   * @see https://wodby.com/docs/infrastructure/env-vars/
   */
  public function getEnvs(): array
  {
    return [
      'APP_ENV' => $this->getAppEnv(),
      'DRUPAL_DB_DRIVER' => getenv('DB_DRIVER'),
      'DRUPAL_DB_NAME' => getenv('DB_NAME'),
      'DRUPAL_DB_USER' => getenv('DB_USER'),
      'DRUPAL_DB_PASS' => getenv('DB_PASSWORD'),
      'DRUPAL_DB_HOST' => getenv('DB_HOST'),
      'DRUPAL_DB_PORT' => getenv('DB_PORT'),
      'DRUPAL_DB_PREFIX' => getenv('DB_PREFIX'),
      'DRUPAL_ROUTES' => getenv('WODBY_URL_PRIMARY'),
    ];
  }

  public function getEjectedCode(): string
  {
    return <<<'PHP'
$app_env = getenv('APP_ENV') ?: (getenv('WODBY_INSTANCE_TYPE') ?: 'dev');

$databases['default']['default'] = [
  'driver' => getenv('DB_DRIVER') ?: 'mysql',
  'database' => getenv('DB_NAME'),
  'username' => getenv('DB_USER'),
  'password' => getenv('DB_PASSWORD'),
  'host' => getenv('DB_HOST'),
  'port' => getenv('DB_PORT') ?: 3306,
  'prefix' => getenv('DB_PREFIX') ?: '',
  'init_commands' => [
    'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
  ],
];

$routes = array_values(array_filter([getenv('WODBY_URL_PRIMARY') ?: null]));
PHP;
  }
}
