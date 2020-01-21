<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

use Druidfi\Omen\DrupalEnvDetector;

class AmazeeIoLegacy extends EnvMappingAbstract
{
  public function getAppEnv() {
    if (getenv('APP_ENV')) {
      return getenv('APP_ENV');
    }
    else if (getenv('AMAZEEIO_SITE_ENVIRONMENT') === 'development') {
      return DrupalEnvDetector::ENV_DEVELOPMENT;
    }
    else if (getenv('AMAZEEIO_SITE_ENVIRONMENT') === 'production') {
      return DrupalEnvDetector::ENV_PRODUCTION;
    }
  }

  public function getEnvs() : array {
    return [
      'APP_ENV' => $this->getAppEnv(),
      'HOSTNAME' => getenv('HOSTNAME'),
      'DRUPAL_DB_NAME' => getenv('AMAZEEIO_SITENAME'),
      'DRUPAL_DB_USER' => getenv('AMAZEEIO_DB_USERNAME'),
      'DRUPAL_DB_PASS' => getenv('AMAZEEIO_DB_PASSWORD'),
      'DRUPAL_DB_HOST' => getenv('AMAZEEIO_DB_HOST'),
      'DRUPAL_DB_PORT' => getenv('AMAZEEIO_DB_PORT'),
      'DRUPAL_TMP_PATH' => getenv('AMAZEEIO_TMP_PATH'),
    ];
  }
}
