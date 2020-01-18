<?php declare(strict_types=1);

namespace Druidfi\Omen;

use Druidfi\Omen\EnvMapping\AmazeeIoLegacy;
use Druidfi\Omen\EnvMapping\EnvMappingAbstract;
use Druidfi\Omen\EnvMapping\Lagoon;
use Druidfi\Omen\EnvMapping\Lando;
use Druidfi\Omen\EnvMapping\Pantheon;
use Druidfi\Omen\EnvMapping\Wodby;

class DrupalEnvDetector
{
  const CMI_PATH = 'conf/cmi';
  const DEFAULT_APP_ENV = 'prod';
  const DS = DIRECTORY_SEPARATOR;

  const MAP = [
    'AMAZEEIO_SITENAME' => AmazeeIoLegacy::class,
    'LAGOON' => Lagoon::class,
    'LANDO_INFO' => Lando::class,
    'PANTHEON_ENVIRONMENT' => Pantheon::class,
    'WODBY_INSTANCE_TYPE' => Wodby::class,
  ];

  private $config = [];
  private $config_directories = [];
  private $databases = [];

  /**
   * @var EnvMappingAbstract
   */
  private $omen;
  private $settings = [];

  public function __construct($settings_dir) {
    global $config, $config_directories, $databases, $settings;

    $this->config = &$config;
    $this->config_directories = &$config_directories;
    $this->databases = &$databases;
    $this->settings = &$settings;

    // Do the detection!
    foreach (self::MAP as $env_key => $class) {
      if (getenv($env_key)) {
        $this->omen = new $class();
        break;
      }
    }

    // Set mapped env variables
    foreach ($this->omen->getEnvs() as $env_var => $env_val) {
      putenv($env_var . '='. $env_val);
    }

    // APP_ENV: dev|test|prod
    $APP_ENV = getenv('APP_ENV') ?: self::DEFAULT_APP_ENV;

    // Env specific default values
    $this->setEnvDefaults($APP_ENV);

    // Load/add files (if exist) from sites/default in following order:
    foreach (['all', $APP_ENV, 'local'] as $set) {
      // all.settings.php, dev.settings.php and local.settings.php
      if (file_exists($settings_dir . self::DS . $set . '.settings.php')) {
        include $settings_dir . self::DS . $set . '.settings.php';
      }

      // all.services.yml, dev.services.yml and local.services.yml
      if (file_exists($settings_dir . self::DS . $set . '.services.yml')) {
        $settings['container_yamls'][] = $settings_dir . self::DS . $set . '.services.yml';
      }
    }

    $this->setGlobalDefaults();

    $this->setDatabaseConnection();
  }

  /**
   * Return modified configuration.
   *
   * @return array
   */
  public function getConfiguration() : array {
    return [
      'config' => (array) $this->config,
      'config_directories' => (array) $this->config_directories,
      'databases' => (array) $this->databases,
      'settings' => (array) $this->settings,
    ];
  }

  /**
   * Set ENV specific default values.
   *
   * @param string $APP_ENV
   */
  private function setEnvDefaults(string $APP_ENV) {
    $defaults_path = __DIR__ . self::DS . 'defaults' . self::DS;
    $defaults_file = $APP_ENV . '.php';

    $defaults = require $defaults_path . $defaults_file;

    foreach ($defaults as $set => $values) {
      if (!is_array($this->{$set})) {
        $this->{$set} = [];
      }

      $this->{$set} = array_merge($this->{$set}, $values);
    }
  }

  /**
   * Set global values. Same for all environments.
   */
  private function setGlobalDefaults() {
    // Load curated default values for detected ENV
    // Set directory for loading CMI configuration.
    $this->config_directories['config_sync_directory'] = '../' . self::CMI_PATH;
    // In Drupal 8.8 this is in $settings array.
    $this->settings['config_sync_directory'] = '../' . self::CMI_PATH;

    // Hash salt.
    $this->settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') ?: '0000000000000000';

    // Public files path
    $this->settings['file_public_path'] = 'sites/default/files';

    // Private files path
    $this->settings['file_private_path'] = FALSE;

    // Trusted Host Patterns, see https://www.drupal.org/node/2410395 for more information.
    // If your site runs on multiple domains, you need to add these domains here
    $host = str_replace('.', '\.', getenv('HOSTNAME'));
    $this->settings['trusted_host_patterns'][] = '^' . $host . '$';
  }

  /**
   * Set database connection.
   */
  private function setDatabaseConnection() {
    // DRUPAL_DB_* should be defined at this point.
    $this->databases['default']['default'] = [
      'driver' => getenv('DRUPAL_DB_DRIVER') ?: 'mysql',
      'database' => getenv('DRUPAL_DB_NAME') ?: 'drupal',
      'username' => getenv('DRUPAL_DB_USER') ?: 'drupal',
      'password' => getenv('DRUPAL_DB_PASS') ?: 'drupal',
      'host' => getenv('DRUPAL_DB_HOST') ?: 'db',
      'port' => getenv('DRUPAL_DB_PORT') ?: 3306,
      'prefix' => getenv('DRUPAL_DB_PREFIX') ?: '',
    ];
  }
}
