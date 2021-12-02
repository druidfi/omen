<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

use Druidfi\Omen\DrupalEnvDetector;

class Pantheon extends EnvMappingAbstract
{
  protected string $env_name = 'PANTHEON_ENVIRONMENT';
  protected array $env_type_map = [
    'live' => DrupalEnvDetector::ENV_PRODUCTION,
  ];

  /**
   * @see https://pantheon.io/docs/read-environment-config
   */
  public function getEnvs() : array {
    return [
      'APP_ENV' => $this->getAppEnv(),
      'DRUPAL_DB_NAME' => getenv('DB_NAME'),
      'DRUPAL_DB_USER' => getenv('DB_USER'),
      'DRUPAL_DB_PASS' => getenv('DB_PASSWORD'),
      'DRUPAL_DB_HOST' => getenv('DB_HOST'),
      'DRUPAL_DB_PORT' => getenv('DB_PORT'),
      'DRUPAL_ROUTES' => 'http://'. getenv('PANTHEON_ENVIRONMENT') .'-'. getenv('PANTHEON_SITE_NAME') . '.pantheon.io',
    ];
  }
}
