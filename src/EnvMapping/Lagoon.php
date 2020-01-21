<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

use Druidfi\Omen\DrupalEnvDetector;

class Lagoon extends EnvMappingAbstract
{
  protected $env_name = 'LAGOON_ENVIRONMENT_TYPE';
  protected $env_type_map = [
    'development' => DrupalEnvDetector::ENV_DEVELOPMENT,
    'production' => DrupalEnvDetector::ENV_PRODUCTION,
  ];

  public function getEnvs() : array {
    return [
      'APP_ENV' => $this->getAppEnv(),
      'DRUPAL_DB_NAME' => getenv('MARIADB_DATABASE'),
      'DRUPAL_DB_USER' => getenv('MARIADB_USERNAME'),
      'DRUPAL_DB_PASS' => getenv('MARIADB_PASSWORD'),
      'DRUPAL_DB_HOST' => getenv('MARIADB_HOST'),
      'DRUPAL_ROUTES' => getenv('LAGOON_ROUTES'),
    ];
  }
}
