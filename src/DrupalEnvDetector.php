<?php declare(strict_types=1);

namespace Druidfi\Omen;

use Druidfi\Omen\EnvMapping\AmazeeIoLegacy;
use Druidfi\Omen\EnvMapping\EnvMappingAbstract;
use Druidfi\Omen\EnvMapping\Lagoon;
use Druidfi\Omen\EnvMapping\Lando;
use Druidfi\Omen\EnvMapping\Pantheon;
use Druidfi\Omen\EnvMapping\Wodby;
use ReflectionClass;

class DrupalEnvDetector
{
  const CMI_PATH = 'conf/cmi';
  const DEFAULT_APP_ENV = self::ENV_PRODUCTION;
  const DS = DIRECTORY_SEPARATOR;
  const ENV_DEVELOPMENT = 'dev';
  const ENV_PRODUCTION = 'prod';

  const MAP = [
    'LAGOON' => Lagoon::class, // Must be before AmazeeIoLegacy
    'AMAZEEIO_SITENAME' => AmazeeIoLegacy::class,
    'LANDO_INFO' => Lando::class,
    'PANTHEON_ENVIRONMENT' => Pantheon::class,
    'WODBY_INSTANCE_TYPE' => Wodby::class,
  ];

  private $app_env = self::DEFAULT_APP_ENV;
  private $config = [];
  private $config_directories = [];
  private $databases = [];
  private $drupal_version = '';

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

    // Detect Drupal version.
    $this->drupal_version = (new ReflectionClass('Drupal'))->getConstants()['VERSION'];

    // Do the detection!
    foreach (self::MAP as $env_key => $class) {
      if (getenv($env_key)) {
        $this->omen = new $class();
        // Break on first detected.
        break;
      }
    }

    // Set mapped env variables IF we have detected something
    if (!is_null($this->omen)) {
      foreach ($this->omen->getEnvs() as $env_var => $env_val) {
        putenv($env_var . '=' . $env_val);
      }
    }

    // APP_ENV: dev|test|prod
    $this->app_env = getenv('APP_ENV') ?: self::DEFAULT_APP_ENV;

    // Env specific default values
    $this->setEnvDefaults();

    // Load/add files (if exist) from sites/default in following order:
    foreach (['all', $this->app_env, 'local'] as $set) {
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
    $this->setTrustedHostPatterns();
    $this->setDatabaseConnection();
  }

  /**
   * Return modified configuration.
   *
   * @return array
   */
  public function getConfiguration() : array {
    $conf = [
      'config' => (array) $this->config,
      'databases' => (array) $this->databases,
      'settings' => (array) $this->settings,
    ];

    if (!empty($this->config_directories)) {
      $conf['config_directories'] = (array) $this->config_directories;
    }

    return $conf;
  }

  /**
   * Print out configuration.
   */
  public function showConfiguration() {
    $conf = $this->getConfiguration();
    echo '<h1>Drupal: '. $this->drupal_version .', APP_ENV: '. $this->app_env .'</h1>';
    echo '<pre>';
    echo '<h2>$config</h2>';
    echo json_encode($conf['config'], JSON_PRETTY_PRINT);
    echo '<h2>$databases</h2>';
    echo json_encode($conf['databases'], JSON_PRETTY_PRINT);
    echo '<h2>$settings</h2>';
    echo json_encode($conf['settings'], JSON_PRETTY_PRINT);
    if (isset($conf['config_directories'])) {
      echo '<h2>$config_directories (deprecated in Drupal 8.8)</h2>';
      echo json_encode($conf['config_directories'], JSON_PRETTY_PRINT);
    }
    echo '</pre>';
    exit();
  }

  /**
   * Set ENV specific default values.
   */
  private function setEnvDefaults() {
    $class = "Druidfi\Omen\EnvDefaults\\". ucfirst($this->app_env) ."Defaults";
    $env_defaults = (new $class())->getDefaults();

    foreach ($env_defaults as $set => $values) {
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
    // Set directory for loading CMI configuration.
    if (version_compare($this->drupal_version, '8.0.0', '<')) {
      $this->config_directories['config_sync_directory'] = '../' . self::CMI_PATH;
    }

    // In Drupal 8.8 this is in $settings array.
    $this->settings['config_sync_directory'] = '../' . self::CMI_PATH;

    // Hash salt.
    $this->settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') ?: '0000000000000000';

    // Public files path.
    $this->settings['file_public_path'] = 'sites/default/files';

    // Private files path.
    $this->settings['file_private_path'] = getenv('DRUPAL_FILES_PRIVATE') ?: FALSE;

    // Temp path.
    $this->config['system.file']['path']['temporary'] = getenv('DRUPAL_TMP_PATH') ?: '/tmp';
  }

  /**
   * Set trusted host patterns.
   *
   * @see https://www.drupal.org/node/2410395
   */
  private function setTrustedHostPatterns() {
    $this->settings['trusted_host_patterns'] = [];

    // Drupal route(s).
    $routes = (getenv('DRUPAL_ROUTES')) ? explode(',', getenv('DRUPAL_ROUTES')) : [];

    foreach ($routes as $route) {
      $host = str_replace('.', '\.', parse_url($route)['host']);
      $this->settings['trusted_host_patterns'][] = '^' . $host . '$';
    }

    if (getenv('DRUSH_OPTIONS_URI') && !in_array(getenv('DRUSH_OPTIONS_URI'), $routes)) {
      $host = str_replace('.', '\.', parse_url(getenv('DRUSH_OPTIONS_URI'))['host']);
      $this->settings['trusted_host_patterns'][] = '^' . $host . '$';
    }
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
