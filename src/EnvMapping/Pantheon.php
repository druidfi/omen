<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

class Pantheon extends EnvMappingAbstract
{
  public function getAppEnv() {
    if (getenv('PANTHEON_ENVIRONMENT') === 'live') {
      return 'prod';
    }
    else {
      return getenv('PANTHEON_ENVIRONMENT');
    }
  }

  /**
   * @see https://pantheon.io/docs/read-environment-config
   */
  public function getEnvs() : array {
    return [
      'APP_ENV' => $this->getAppEnv(),
      'HOSTNAME' => getenv('PANTHEON_ENVIRONMENT') .'-'. getenv('PANTHEON_SITE_NAME') . '.pantheon.io',
      'DRUPAL_DB_NAME' => getenv('DB_NAME'),
      'DRUPAL_DB_USER' => getenv('DB_USER'),
      'DRUPAL_DB_PASS' => getenv('DB_PASSWORD'),
      'DRUPAL_DB_HOST' => getenv('DB_HOST'),
      'DRUPAL_DB_PORT' => getenv('DB_PORT'),
    ];
  }
}
