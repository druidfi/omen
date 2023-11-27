<?php

namespace Druidfi\Omen\System;

class Lando extends AbstractSystem
{
  /**
   * @see https://github.com/lando/lando/blob/main/docs/config/env.md
   */
  public function getEnvs(): array
  {
    $lando_info = $this->getLandoInfo();
    $lando_host = $this->getLandoHost();

    return [
      'APP_ENV' => $this->getAppEnv(),
      'DRUPAL_DB_NAME' => $lando_info['database']['creds']['database'],
      'DRUPAL_DB_USER' => $lando_info['database']['creds']['user'],
      'DRUPAL_DB_PASS' => $lando_info['database']['creds']['password'],
      'DRUPAL_DB_HOST' => $lando_info['database']['internal_connection']['host'],
      'DRUPAL_DB_PORT' => $lando_info['database']['internal_connection']['port'],
      'DRUPAL_HASH_SALT' => getenv('HASH_SALT'),
      'DRUPAL_ROUTES' => sprintf('http://%s,https://%s', $lando_host, $lando_host),
    ];
  }

  private function getLandoHost(): string
  {
    return getenv('LANDO_APP_NAME') . '.' . getenv('LANDO_DOMAIN');
  }

  private function getLandoInfo(): array
  {
    return json_decode(getenv('LANDO_INFO'), true);
  }
}
