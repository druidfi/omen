<?php

namespace Druidfi\Omen\EnvMapping;

class Ddev extends EnvMappingAbstract
{
  public function getEnvs() : array
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

  public function setConfiguration(&$config, &$settings)
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
}
