<?php declare(strict_types=1);

namespace Druidfi\Omen;

use Druidfi\Omen\EnvMapping\Ddev;
use Druidfi\Omen\EnvMapping\EnvMappingAbstract;
use Druidfi\Omen\EnvMapping\Lagoon;
use Druidfi\Omen\EnvMapping\Lando;
use Druidfi\Omen\EnvMapping\Pantheon;
use Druidfi\Omen\EnvMapping\Wodby;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

class DrupalEnvDetector
{
  const CMI_PATH = 'conf/cmi';
  const DEFAULT_APP_ENV = self::ENV_PRODUCTION;
  const DS = DIRECTORY_SEPARATOR;
  const ENV_DEVELOPMENT = 'dev';
  const ENV_PRODUCTION = 'prod';

  const MAP = [
    'LAGOON' => Lagoon::class,
    'IS_DDEV_PROJECT' => Ddev::class,
    'LANDO_INFO' => Lando::class,
    'PANTHEON_ENVIRONMENT' => Pantheon::class,
    'WODBY_INSTANCE_TYPE' => Wodby::class,
  ];

  private $app_env;
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

    // Handle HTTPS
    if (getenv('HTTPS') !== 'on' && getenv('HTTP_X_FORWARDED_PROTO') === 'https') {
      $_SERVER['HTTPS'] = 'on';
      $_SERVER["SERVER_PORT"] = getenv('HTTP_X_FORWARDED_PORT') ?: 443;
    }

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

      // Set Env specific configuration
      $this->omen->setConfiguration($config, $settings);
    }
    else {
      // Set reverse proxy automatically for other environments
      if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwarded = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        // Client IP is the most left one on HTTP_X_FORWARDED_FOR
        $client_ip = array_shift($forwarded);

        if ($_SERVER['REMOTE_ADDR'] !== $client_ip) {
          $settings['reverse_proxy'] = TRUE;
          $settings['reverse_proxy_addresses'] = (!empty($forwarded)) ? $forwarded : [$_SERVER['REMOTE_ADDR']];
          $settings['reverse_proxy_trusted_headers'] = Request::HEADER_X_FORWARDED_ALL;
        }
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

    if (getenv('OMEN_TOKEN') && isset($_GET['_show_omens'])) {
      if ($_GET['_show_omens'] == getenv('OMEN_TOKEN')) {
        $this->printConfiguration($conf);
      }
    }

    return $conf;
  }

  /**
   * Print out configuration.
   */
  public function showConfiguration() {
    $conf = $this->getConfiguration();
    $this->printConfiguration($conf);
  }

  protected function printConfiguration($conf) {
    $omen = is_null($this->omen) ? '[NOT_ANY_DETECTED_SYSTEM]' : get_class($this->omen);
    echo '<h1>Drupal: '. $this->drupal_version .', APP_ENV: '. $this->app_env .' on '. $omen .'</h1>';
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
    $older_than_88 = version_compare($this->drupal_version, '8.8.0', '<');

    // Set directory for loading CMI configuration.
    if ($older_than_88) {
      $this->config_directories['sync'] = '../' . self::CMI_PATH;
    }

    // In Drupal 8.8 this is in $settings array.
    $this->settings['config_sync_directory'] = '../' . self::CMI_PATH;

    // Hash salt.
    $this->settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') ?: $this->settings['hash_salt'] ?? '0000000000000000';

    // Public files path.
    $this->settings['file_public_path'] = $this->settings['file_public_path'] ?? 'sites/default/files';

    // Private files path.
    $this->settings['file_private_path'] = getenv('DRUPAL_FILES_PRIVATE') ?: $this->settings['file_private_path'] ?? FALSE;

    // Temp path.
    $this->settings['file_temp_path'] = getenv('DRUPAL_TMP_PATH') ?: $this->settings['file_temp_path'] ?? '/tmp';

    if ($older_than_88) {
      $this->config['system.file']['path']['temporary'] = $this->settings['file_temp_path'];
    }

    // Exclude these modules from configuration export if Drupal 8.8+.
    if (!$older_than_88) {
      $this->settings['config_exclude_modules'] = ['devel', 'stage_file_proxy'];
    }
  }

  /**
   * Set trusted host patterns.
   *
   * @see https://www.drupal.org/node/2410395
   */
  private function setTrustedHostPatterns() {
    $this->settings['trusted_host_patterns'] = [];
    $hosts = [];

    // Drupal route(s).
    $routes = (getenv('DRUPAL_ROUTES')) ? explode(',', getenv('DRUPAL_ROUTES')) : [];

    foreach ($routes as $route) {
      $hosts[] = $host = parse_url($route)['host'];
      $trusted_host = str_replace('.', '\.', $host);
      $this->settings['trusted_host_patterns'][] = '^' . $trusted_host . '$';
    }

    $drush_options_uri = getenv('DRUSH_OPTIONS_URI');

    if ($drush_options_uri && !in_array($drush_options_uri, $routes)) {
      $host = str_replace('.', '\.', parse_url($drush_options_uri)['host']);
      $this->settings['trusted_host_patterns'][] = '^' . $host . '$';
    }

    // If not explicitly set, use first host as DRUSH_OPTIONS_URI
    if (!$drush_options_uri) {
      putenv('DRUSH_OPTIONS_URI=http://' . $hosts[0]);
    }

    if (!is_null($this->omen) && method_exists($this->omen, 'getTrustedHostPatterns')) {
      $patterns = $this->omen->getTrustedHostPatterns();
      $this->settings['trusted_host_patterns'] = array_merge($this->settings['trusted_host_patterns'], $patterns);
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
