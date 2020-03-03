<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

class Ddev extends EnvMappingAbstract
{
  public function getEnvs() : array {
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

  protected function getRoutes() {
    $sheme = (getenv('HTTPS') === 'on') ? 'https' : 'http';
    $hosts = explode(',', getenv('VIRTUAL_HOST'));
    $hosts = array_filter(array_unique($hosts));

    foreach ($hosts as $host) {
      $routes[] = $sheme . '://' . $host;
    }

    return join(',', $routes);
  }

  public function getTrustedHostPatterns() {
    return [
      '^.+\.ddev\.site',
    ];
  }
}
