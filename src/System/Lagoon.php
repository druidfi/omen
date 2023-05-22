<?php

namespace Druidfi\Omen\System;

use Druidfi\Omen\Reader;

/**
 * @see https://github.com/amazeeio/drupal-example/blob/master/web/sites/default/settings.php
 */
class Lagoon extends AbstractSystem
{
  protected string $env_name = 'LAGOON_ENVIRONMENT_TYPE';
  protected array $env_type_map = [
    'development' => Reader::ENV_DEVELOPMENT,
    'production' => Reader::ENV_PRODUCTION,
  ];

  public function setConfiguration(&$config, &$settings): void
  {
    $config = [];

    if (getenv('SOLR_HOST')) {
      $config['search_api.server.solr']['backend_config']['connector_config']['host'] = getenv('SOLR_HOST');
      $config['search_api.server.solr']['backend_config']['connector_config']['path'] = '/solr/';
      $config['search_api.server.solr']['backend_config']['connector_config']['core'] = getenv('SOLR_CORE') ?: 'drupal';
      $config['search_api.server.solr']['backend_config']['connector_config']['port'] = 8983;
      $config['search_api.server.solr']['backend_config']['connector_config']['http_user'] = (getenv('SOLR_USER') ?: '');
      $config['search_api.server.solr']['backend_config']['connector_config']['http']['http_user'] = (getenv('SOLR_USER') ?: '');
      $config['search_api.server.solr']['backend_config']['connector_config']['http_pass'] = (getenv('SOLR_PASSWORD') ?: '');
      $config['search_api.server.solr']['backend_config']['connector_config']['http']['http_pass'] = (getenv('SOLR_PASSWORD') ?: '');
      $config['search_api.server.solr']['name'] = 'Lagoon Solr - Environment: ' . getenv('LAGOON_PROJECT');
    }

    $settings['reverse_proxy'] = true;
  }

  public function getEnvs(): array
  {
    return [
      'APP_ENV' => $this->getAppEnv(),
      'DRUPAL_DB_NAME' => getenv('MARIADB_DATABASE'),
      'DRUPAL_DB_USER' => getenv('MARIADB_USERNAME'),
      'DRUPAL_DB_PASS' => getenv('MARIADB_PASSWORD'),
      'DRUPAL_DB_HOST' => getenv('MARIADB_HOST') ?: 'mariadb',
      'DRUPAL_HASH_SALT' => hash('sha256', getenv('LAGOON_PROJECT')),
      'DRUPAL_ROUTES' => $this->getRoutes(),
      'DRUPAL_TMP_PATH' => getenv('TMP'),
    ];
  }

  protected function getRoutes(): string
  {
    $routes_string = getenv('LAGOON_ROUTE') .','. getenv('LAGOON_ROUTES');
    $routes = explode(',', $routes_string);
    $routes = array_filter(array_unique($routes));
    return join(',', $routes);
  }

  public function getTrustedHostPatterns(): array
  {
    return [
      '^.+\.docker\.amazee\.io$',
    ];
  }
}
