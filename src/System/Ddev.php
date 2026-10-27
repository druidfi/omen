<?php

namespace Druidfi\Omen\System;

class Ddev extends AbstractSystem
{
  public function getEnvs(): array
  {
    return [
      'APP_ENV' => $this->getAppEnv(),
      'DRUPAL_DB_NAME' => 'db',
      'DRUPAL_DB_USER' => 'db',
      'DRUPAL_DB_PASS' => 'db',
      'DRUPAL_DB_HOST' => 'db',
      'DRUPAL_DB_PORT' => 3306,
      'DRUPAL_ROUTES' => $this->getRoutes(),
    ];
  }

  public function setConfiguration(&$config, &$settings): void
  {
    // Don't use Symfony's APCLoader. ddev includes APCu; Composer's APCu loader has
    // better performance.
    $settings['class_loader_auto_detect'] = false;

    $settings['config_sync_directory'] = 'sites/default/files/sync';
  }

  protected function getRoutes(): string
  {
    $routes = [];
    $scheme = (getenv('HTTPS') === 'on') ? 'https' : 'http';
    $hosts = explode(',', getenv('VIRTUAL_HOST'));
    $hosts = array_filter(array_unique($hosts));

    foreach ($hosts as $host) {
      $routes[] = $scheme . '://' . $host;
    }

    return join(',', $routes);
  }

  public function getTrustedHostPatterns(): array
  {
    return [
      '^.+\.ddev\.site',
    ];
  }

  public function getEjectedCode(): string
  {
    return <<<'PHP'
$app_env = getenv('APP_ENV') ?: (getenv('LOCAL_ENV_TYPE') ?: 'dev');

$databases['default']['default'] = [
  'driver' => 'mysql',
  'database' => 'db',
  'username' => 'db',
  'password' => 'db',
  'host' => 'db',
  'port' => '3306',
  'prefix' => '',
  'init_commands' => [
    'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
  ],
];

// Don't use Symfony's APCLoader. ddev includes APCu; Composer's APCu loader has
// better performance.
$settings['class_loader_auto_detect'] = false;
$settings['config_sync_directory'] = 'sites/default/files/sync';

$ddev_scheme = (getenv('HTTPS') === 'on') ? 'https' : 'http';
$ddev_hosts = array_filter(array_unique(explode(',', (string) getenv('VIRTUAL_HOST'))));
$routes = array_values(array_map(static fn ($host) => $ddev_scheme . '://' . $host, $ddev_hosts));

$system_trusted_host_patterns = [
  '^.+\.ddev\.site',
];
PHP;
  }
}
