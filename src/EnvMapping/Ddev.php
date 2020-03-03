<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

class Ddev extends EnvMappingAbstract
{
  public function getEnvs() : array {
    $sheme = (getenv('HTTPS') === 'on') ? 'https' : 'http';

    return [
      'APP_ENV' => $this->getAppEnv(),
      'DRUPAL_DB_NAME' => 'db',
      'DRUPAL_DB_USER' => 'db',
      'DRUPAL_DB_PASS' => 'db',
      'DRUPAL_DB_HOST' => 'db',
      'DRUPAL_DB_PORT' => 3306,
      'DRUPAL_ROUTES' => $sheme . '://' . getenv('VIRTUAL_HOST'),
    ];
  }

  public function getTrustedHostPatterns() {
    return [
      '^.+\.ddev\.site',
    ];
  }
}
