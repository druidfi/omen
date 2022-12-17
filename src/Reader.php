<?php

namespace Druidfi\Omen;

use Druidfi\Omen\EnvMapping\Ddev;
use Druidfi\Omen\EnvMapping\EnvMappingAbstract;
use Druidfi\Omen\EnvMapping\Lagoon;
use Druidfi\Omen\EnvMapping\Lando;
use Druidfi\Omen\EnvMapping\Pantheon;
use Druidfi\Omen\EnvMapping\Tugboat;
use Druidfi\Omen\EnvMapping\Wodby;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

class Reader
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
    'TUGBOAT_PREVIEW_ID' => Tugboat::class,
    'WODBY_INSTANCE_TYPE' => Wodby::class,
  ];

  private $app_env;
  private ?string $app_root;
  private ?array $config = [];
  private ?array $databases = [];
  private string $drupal_version;

  /**
   * @var EnvMappingAbstract
   */
  private $omen;
  private ?array $settings = [];

  public function __construct(array $vars)
  {
    unset($vars['class_loader']);
    extract($vars);
    unset($vars);

    $settings_dir = $app_root . self::DS . $site_path;

    $this->app_root = $app_root;
    $this->config = &$config;
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
          $settings['reverse_proxy_trusted_headers'] =
            Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_FORWARDED;
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

  public static function get(array $vars) : array
  {
    return (new Reader($vars))->getConf();
  }

  /**
   * Get read configuration.
   *
   * @return array
   */
  public function getConf() : array
  {
    $conf = [
      'config' => $this->config,
      'databases' => $this->databases,
      'settings' => $this->settings,
    ];

    if (!empty($this->config_directories)) {
      $conf['config_directories'] = $this->config_directories;
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
  public static function show(array $vars)
  {
    $reader = new Reader($vars);
    $reader->printConfiguration($reader->getConf());
  }

  protected function printConfiguration($conf)
  {
    $omen = is_null($this->omen) ? '[NOT_ANY_DETECTED_SYSTEM]' : get_class($this->omen);
    echo '<h1>Drupal: '. $this->drupal_version .', APP_ENV: '. $this->app_env .' on '. $omen .'</h1>';
    echo '<pre>';
    echo '<h2>$config</h2>';
    echo json_encode($conf['config'], JSON_PRETTY_PRINT);
    echo '<h2>$databases</h2>';
    echo json_encode($conf['databases'], JSON_PRETTY_PRINT);
    echo '<h2>$settings</h2>';
    echo json_encode($conf['settings'], JSON_PRETTY_PRINT);
    echo '</pre>';
    exit();
  }

  /**
   * Set ENV specific default values.
   */
  private function setEnvDefaults()
  {
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
  private function setGlobalDefaults()
  {
    // Set directory for loading CMI configuration.
    $this->settings['config_sync_directory'] = getenv('DRUPAL_SYNC_DIR')
      ?: $this->settings['config_sync_directory'] ?: '../' . self::CMI_PATH;

    // Hash salt.
    $this->settings['hash_salt'] = getenv('DRUPAL_HASH_SALT')
      ?: $this->settings['hash_salt'] ?: '0000000000000000';

    // Public files path.
    $this->settings['file_public_path'] = $this->settings['file_public_path'] ?? 'sites/default/files';

    // Private files path.
    $this->settings['file_private_path'] = getenv('DRUPAL_FILES_PRIVATE')
      ?: $this->settings['file_private_path'] ?: FALSE;

    // Temp path.
    $this->settings['file_temp_path'] = getenv('DRUPAL_TMP_PATH') ?: $this->settings['file_temp_path'] ?? '/tmp';
  }

  /**
   * Set trusted host patterns.
   *
   * @see https://www.drupal.org/node/2410395
   */
  private function setTrustedHostPatterns()
  {
    if (!isset($this->settings['trusted_host_patterns'])) {
      $this->settings['trusted_host_patterns'] = [];
    }

    $hosts = [];

    // Drupal route(s).
    $routes = (getenv('DRUPAL_ROUTES')) ? explode(',', getenv('DRUPAL_ROUTES')) : [];

    foreach ($routes as $route) {
      $host = parse_url($route);

      if (is_array($host)) {
        $hosts[] = $host['host'];
        $trusted_host = str_replace('.', '\.', $host['host']);
        $this->settings['trusted_host_patterns'][] = '^' . $trusted_host . '$';
      }
    }

    $drush_options_uri = getenv('DRUSH_OPTIONS_URI');

    if ($drush_options_uri && !in_array($drush_options_uri, $routes)) {
      $parsed_host = parse_url($drush_options_uri);

      if (is_array($parsed_host)) {
        $host = str_replace('.', '\.', $parsed_host['host']);
        $this->settings['trusted_host_patterns'][] = '^' . $host . '$';
      }
    }

    // If not explicitly set, use first host as DRUSH_OPTIONS_URI
    if (!$drush_options_uri) {
      putenv('DRUSH_OPTIONS_URI=https://' . $hosts[0]);
    }

    if (!is_null($this->omen) && method_exists($this->omen, 'getTrustedHostPatterns')) {
      $patterns = $this->omen->getTrustedHostPatterns();
      $this->settings['trusted_host_patterns'] = array_merge($this->settings['trusted_host_patterns'], $patterns);
    }
  }

  /**
   * Set database connection.
   */
  private function setDatabaseConnection()
  {
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

    $drupal_10 = version_compare($this->drupal_version, '10.0.0', '>=');

    if ($drupal_10) {
      $this->databases['default']['default']['init_commands'] = [
        'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
      ];
    }
  }
}
