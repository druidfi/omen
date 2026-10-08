<?php

namespace Druidfi\Omen\System;

use Druidfi\Omen\Reader;

abstract class AbstractSystem implements SystemInterface
{
  protected string $env_name = 'LOCAL_ENV_TYPE';
  protected array $env_type_map = [];

  public function getAppEnv(): string
  {
    if (getenv('APP_ENV')) {
      return getenv('APP_ENV');
    }

    if (getenv($this->env_name)) {
      foreach ($this->env_type_map as $source => $target) {
        if (getenv($this->env_name) === $source) {
          return $target;
        }
      }

      return getenv($this->env_name);
    }

    return Reader::ENV_DEVELOPMENT;
  }

  public function setConfiguration(&$config, &$settings): void
  {
  }

  public function getEnvs(): array
  {
    return [
      'APP_ENV' => $this->getAppEnv(),
    ];
  }

  public function getEjectedCode(): string
  {
    return <<<'PHP'
$app_env = getenv('APP_ENV') ?: (getenv('LOCAL_ENV_TYPE') ?: 'dev');

$databases['default']['default'] = [
  'driver' => getenv('DRUPAL_DB_DRIVER') ?: 'mysql',
  'database' => getenv('DRUPAL_DB_NAME') ?: 'drupal',
  'username' => getenv('DRUPAL_DB_USER') ?: 'drupal',
  'password' => getenv('DRUPAL_DB_PASS') ?: 'drupal',
  'host' => getenv('DRUPAL_DB_HOST') ?: 'db',
  'port' => getenv('DRUPAL_DB_PORT') ?: 3306,
  'prefix' => getenv('DRUPAL_DB_PREFIX') ?: '',
  'init_commands' => [
    'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
  ],
];
PHP;
  }
}
