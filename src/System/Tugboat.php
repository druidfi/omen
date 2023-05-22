<?php

namespace Druidfi\Omen\System;

class Tugboat extends AbstractSystem
{
  /**
   * @see https://docs.tugboat.qa/starter-configs/tutorials/drupal-9/
   */
  public function getEnvs(): array
  {
    return [
      'APP_ENV' => 'prod',
      'DRUPAL_DB_DRIVER' => 'mysql',
      'DRUPAL_DB_NAME' => 'tugboat',
      'DRUPAL_DB_USER' => 'tugboat',
      'DRUPAL_DB_PASS' => 'tugboat',
      'DRUPAL_DB_HOST' => 'mysql',
      'DRUPAL_DB_PORT' => '3306',
      'DRUPAL_HASH_SALT' => hash('sha256', getenv('TUGBOAT_REPO_ID')),
      'DRUPAL_ROUTES' => getenv('TUGBOAT_DEFAULT_SERVICE_URL'),
    ];
  }
}
