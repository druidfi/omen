<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

class Wodby extends EnvMappingAbstract
{
  public function getAppEnv() {
    if (getenv('APP_ENV')) {
      return getenv('APP_ENV');
    }

    return getenv('WODBY_INSTANCE_TYPE');
  }

  /**
   * @see https://wodby.com/docs/infrastructure/env-vars/
   */
  public function getEnvs() : array {
    return [
      'APP_ENV' => $this->getAppEnv(),
      'HOSTNAME' => getenv('WODBY_HOST_PRIMARY'),
      'DRUPAL_DB_DRIVER' => getenv('DB_DRIVER'),
      'DRUPAL_DB_NAME' => getenv('DB_NAME'),
      'DRUPAL_DB_USER' => getenv('DB_USER'),
      'DRUPAL_DB_PASS' => getenv('DB_PASSWORD'),
      'DRUPAL_DB_HOST' => getenv('DB_HOST'),
      'DRUPAL_DB_PORT' => getenv('DB_PORT'),
      'DRUPAL_DB_PREFIX' => getenv('DB_PREFIX'),
    ];
  }
}
