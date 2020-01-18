<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

class Lagoon extends EnvMappingAbstract
{
  protected function getAppEnv() {
    if (getenv('LAGOON_ENVIRONMENT_TYPE') === 'development') {
      return 'dev';
    }
    else if (getenv('LAGOON_ENVIRONMENT_TYPE') === 'production') {
      return 'prod';
    }
  }

  protected function getEnvs() : array {
    return [
      'APP_ENV' => $this->getAppEnv(),
      'HOSTNAME' => getenv('HOSTNAME'),
      'DRUPAL_DB_NAME' => getenv('MARIADB_DATABASE'),
      'DRUPAL_DB_USER' => getenv('MARIADB_USERNAME'),
      'DRUPAL_DB_PASS' => getenv('MARIADB_PASSWORD'),
      'DRUPAL_DB_HOST' => getenv('MARIADB_HOST'),
    ];
  }
}
