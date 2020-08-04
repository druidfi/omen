<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

use Druidfi\Omen\DrupalEnvDetector;

/**
 * @see https://github.com/amazeeio/drupal-setting-files/blob/master/Drupal8/sites/default/settings.php
 */
class AmazeeIoLegacy extends EnvMappingAbstract
{
  protected $env_name = 'AMAZEEIO_SITE_ENVIRONMENT';
  protected $env_type_map = [
    'development' => DrupalEnvDetector::ENV_DEVELOPMENT,
    'production' => DrupalEnvDetector::ENV_PRODUCTION,
  ];

  public function setConfiguration(&$config, &$settings) {
    if (getenv('AMAZEEIO_SOLR_HOST') && getenv('AMAZEEIO_SOLR_PORT')) {
      $config['search_api.server.solr']['backend_config']['connector_config']['host'] = getenv('AMAZEEIO_SOLR_HOST');
      $config['search_api.server.solr']['backend_config']['connector_config']['path'] = '/solr/';
      $config['search_api.server.solr']['backend_config']['connector_config']['core'] = getenv('AMAZEEIO_SOLR_CORE') ?: getenv('AMAZEEIO_SITENAME');
      $config['search_api.server.solr']['backend_config']['connector_config']['port'] = getenv('AMAZEEIO_SOLR_PORT');
      $config['search_api.server.solr']['backend_config']['connector_config']['http_user'] = (getenv('AMAZEEIO_SOLR_USER') ?: '');
      $config['search_api.server.solr']['backend_config']['connector_config']['http']['http_user'] = (getenv('AMAZEEIO_SOLR_USER') ?: '');
      $config['search_api.server.solr']['backend_config']['connector_config']['http_pass'] = (getenv('AMAZEEIO_SOLR_PASSWORD') ?: '');
      $config['search_api.server.solr']['backend_config']['connector_config']['http']['http_pass'] = (getenv('AMAZEEIO_SOLR_PASSWORD') ?: '');
      $config['search_api.server.solr']['name'] = 'AmazeeIO Solr - Environment: ' . getenv('AMAZEEIO_SITE_ENVIRONMENT');
    }

    if (getenv('AMAZEEIO_VARNISH_HOSTS') && getenv('AMAZEEIO_VARNISH_SECRET')) {
      $varnish_hosts = explode(',', getenv('AMAZEEIO_VARNISH_HOSTS'));
      array_walk($varnish_hosts, function(&$value, $key) { $value .= ':6082'; });

      $settings['reverse_proxy'] = TRUE;
      $settings['reverse_proxy_addresses'] = array_merge(explode(',', getenv('AMAZEEIO_VARNISH_HOSTS')), ['127.0.0.1']);

      $config['varnish.settings']['varnish_control_terminal'] = implode(" ", $varnish_hosts);
      $config['varnish.settings']['varnish_control_key'] = getenv('AMAZEEIO_VARNISH_SECRET');
      $config['varnish.settings']['varnish_version'] = 4;
    }
  }

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
