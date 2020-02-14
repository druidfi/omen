<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

use Druidfi\Omen\DrupalEnvDetector;

class AmazeeIoLegacy extends EnvMappingAbstract
{
  protected $env_name = 'AMAZEEIO_SITE_ENVIRONMENT';
  protected $env_type_map = [
    'development' => DrupalEnvDetector::ENV_DEVELOPMENT,
    'production' => DrupalEnvDetector::ENV_PRODUCTION,
  ];

  public function getEnvs() : array {
    return [
      'APP_ENV' => $this->getAppEnv(),
      'DRUPAL_DB_NAME' => getenv('AMAZEEIO_SITENAME'),
      'DRUPAL_DB_USER' => getenv('AMAZEEIO_DB_USERNAME'),
      'DRUPAL_DB_PASS' => getenv('AMAZEEIO_DB_PASSWORD'),
      'DRUPAL_DB_HOST' => getenv('AMAZEEIO_DB_HOST'),
      'DRUPAL_DB_PORT' => getenv('AMAZEEIO_DB_PORT'),
      'DRUPAL_ROUTES' => getenv('AMAZEEIO_BASE_URL'),
      'DRUPAL_TMP_PATH' => getenv('AMAZEEIO_TMP_PATH'),
      'DRUPAL_HASH_SALT' => getenv('AMAZEEIO_HASH_SALT'),
    ];
  }

  public function getTrustedHostPatterns() {
    return [
      '^.+\.docker\.amazee\.io$',
    ];
  }
}
