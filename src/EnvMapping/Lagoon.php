<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

use Druidfi\Omen\DrupalEnvDetector;

class Lagoon extends EnvMappingAbstract
{
  public function getAppEnv() {
    if (getenv('APP_ENV')) {
      return getenv('APP_ENV');
    }
    else if (getenv('LAGOON_ENVIRONMENT_TYPE') === 'development') {
      return DrupalEnvDetector::ENV_DEVELOPMENT;
    }
    else if (getenv('LAGOON_ENVIRONMENT_TYPE') === 'production') {
      return DrupalEnvDetector::ENV_PRODUCTION;
    }
  }

  public function getEnvs() : array {
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
