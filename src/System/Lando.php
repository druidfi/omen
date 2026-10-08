<?php

namespace Druidfi\Omen\System;

class Lando extends AbstractSystem
{
  /**
   * @see https://github.com/lando/lando/blob/main/docs/config/env.md
   */
  public function getEnvs(): array
  {
    $lando_info = $this->getLandoInfo();
    $lando_host = $this->getLandoHost();

    return [
      'APP_ENV' => $this->getAppEnv(),
      'DRUPAL_DB_NAME' => $lando_info['database']['creds']['database'],
      'DRUPAL_DB_USER' => $lando_info['database']['creds']['user'],
      'DRUPAL_DB_PASS' => $lando_info['database']['creds']['password'],
      'DRUPAL_DB_HOST' => $lando_info['database']['internal_connection']['host'],
      'DRUPAL_DB_PORT' => $lando_info['database']['internal_connection']['port'],
      'DRUPAL_HASH_SALT' => getenv('HASH_SALT'),
      'DRUPAL_ROUTES' => sprintf('http://%s,https://%s', $lando_host, $lando_host),
    ];
  }

  private function getLandoHost(): string
  {
    return getenv('LANDO_APP_NAME') . '.' . getenv('LANDO_DOMAIN');
  }

  private function getLandoInfo(): array
  {
    return json_decode(getenv('LANDO_INFO'), true);
  }

  public function getEjectedCode(): string
  {
    return <<<'PHP'
$app_env = getenv('APP_ENV') ?: (getenv('LOCAL_ENV_TYPE') ?: 'dev');

$lando_info = json_decode((string) getenv('LANDO_INFO'), true) ?: [];
$lando_host = getenv('LANDO_APP_NAME') . '.' . getenv('LANDO_DOMAIN');

$databases['default']['default'] = [
  'driver' => 'mysql',
  'database' => $lando_info['database']['creds']['database'] ?? 'drupal',
  'username' => $lando_info['database']['creds']['user'] ?? 'drupal',
  'password' => $lando_info['database']['creds']['password'] ?? 'drupal',
  'host' => $lando_info['database']['internal_connection']['host'] ?? 'db',
  'port' => (string) ($lando_info['database']['internal_connection']['port'] ?? 3306),
  'prefix' => '',
  'init_commands' => [
    'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
  ],
];

$settings['hash_salt'] = getenv('HASH_SALT') ?: ($settings['hash_salt'] ?? '0000000000000000');

$routes = [
  'http://' . $lando_host,
  'https://' . $lando_host,
];
PHP;
  }
}
