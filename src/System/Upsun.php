<?php

namespace Druidfi\Omen\System;

/**
 * @see https://devcenter.upsun.com/posts/drupal-and-upsun/
 */
class Upsun extends AbstractSystem
{
  protected string $env_name = 'PLATFORM_ENVIRONMENT_TYPE';
  protected array $env_type_map = [
    'production' => 'prod',
    'staging' => 'test',
    'development' => 'dev',
  ];

  public function getEnvs(): array
  {
    $db = $this->getDatabaseCredentials();
    [$primary, $others] = $this->getUpstreamRoutes();

    return [
      'APP_ENV' => $this->getAppEnv(),
      'DRUPAL_DB_NAME' => $db['path'] ?? '',
      'DRUPAL_DB_USER' => $db['username'] ?? '',
      'DRUPAL_DB_PASS' => $db['password'] ?? '',
      'DRUPAL_DB_HOST' => $db['host'] ?? '',
      'DRUPAL_DB_PORT' => $db['port'] ?? 3306,
      'DRUPAL_DB_DRIVER' => $db['scheme'] ?? 'mysql',
      'DRUPAL_HASH_SALT' => getenv('PLATFORM_PROJECT_ENTROPY'),
      'DRUSH_OPTIONS_URI' => $primary,
      'DRUPAL_ROUTES' => implode(',', $others),
    ];
  }

  public function setConfiguration(&$config, &$settings): void
  {
    $settings['reverse_proxy'] = true;

    if ($tree_id = getenv('PLATFORM_TREE_ID')) {
      $settings['deployment_identifier'] = $tree_id;
    }

    if ($app_dir = getenv('PLATFORM_APP_DIR')) {
      $settings['file_private_path'] = $settings['file_private_path'] ?? $app_dir . '/private';
      $settings['file_temp_path'] = $settings['file_temp_path'] ?? $app_dir . '/tmp';
      $settings['php_storage']['default']['directory'] = $settings['php_storage']['default']['directory'] ?? $app_dir . '/private';
      $settings['php_storage']['twig']['directory'] = $settings['php_storage']['twig']['directory'] ?? $app_dir . '/private';
    }
  }

  /**
   * Find the first MySQL/MariaDB/PostgreSQL connection from PLATFORM_RELATIONSHIPS.
   */
  protected function getDatabaseCredentials(): array
  {
    $encoded = getenv('PLATFORM_RELATIONSHIPS');

    if (!$encoded) {
      return [];
    }

    $relationships = json_decode(base64_decode($encoded), true);

    if (!is_array($relationships)) {
      return [];
    }

    foreach ($relationships as $connections) {
      foreach ($connections as $connection) {
        if (isset($connection['scheme']) && in_array($connection['scheme'], ['mysql', 'pgsql'])) {
          return $connection;
        }
      }
    }

    return [];
  }

  /**
   * Split upstream routes into [primary, others].
   *
   * Redirect-type routes are excluded — they never reach Drupal.
   * Primary goes to DRUSH_OPTIONS_URI; others go to DRUPAL_ROUTES for trusted host patterns.
   *
   * @return array{0: string, 1: string[]}
   */
  protected function getUpstreamRoutes(): array
  {
    $encoded = getenv('PLATFORM_ROUTES');

    if (!$encoded) {
      return ['', []];
    }

    $routes = json_decode(base64_decode($encoded), true);

    if (!is_array($routes)) {
      return ['', []];
    }

    $primary = '';
    $others = [];

    foreach ($routes as $url => $route) {
      if (($route['type'] ?? '') !== 'upstream') {
        continue;
      }

      if ($route['primary'] ?? false) {
        $primary = $url;
      }
      else {
        $others[] = $url;
      }
    }

    return [$primary, $others];
  }

  public function getTrustedHostPatterns(): array
  {
    return [
      '^.+\.upsun\.app$',
    ];
  }
}
