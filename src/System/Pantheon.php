<?php

namespace Druidfi\Omen\System;

use Druidfi\Omen\Reader;

class Pantheon extends AbstractSystem
{
  protected string $env_name = 'PANTHEON_ENVIRONMENT';
  protected array $env_type_map = [
    'live' => Reader::ENV_PRODUCTION,
  ];

  /**
   * @see https://pantheon.io/docs/read-environment-config
   */
  public function getEnvs(): array
  {
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
