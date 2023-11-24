<?php

namespace Druidfi\Omen;

use Druidfi\Omen\System\Ddev;
use Druidfi\Omen\System\SystemInterface;
use Druidfi\Omen\System\Lagoon;
use Druidfi\Omen\System\Lando;
use Druidfi\Omen\System\Pantheon;
use Druidfi\Omen\System\Tugboat;
use Druidfi\Omen\System\Wodby;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

class Reader
{
  const DEFAULT_APP_ENV = self::ENV_PRODUCTION;
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

  const DRUPAL_SETTING_DEFAULTS = [
    'config_sync_directory' => '../conf/cmi',
    'file_public_path' => 'sites/default/files',
    'file_private_path' => FALSE,
    'file_temp_path' => '/tmp',
    'hash_salt' => '0000000000000000',
  ];

  private string $app_env;
  private ?string $app_root;
  private ?array $config = [];
  private ?array $databases = [];
  private ?string $drupal_version = null;
  private ?SystemInterface $system = null;
  private ?array $settings = [];

  public function __construct(array $vars)
  {
    unset($vars['class_loader']);
    extract($vars);
    unset($vars);

    $this->app_root = $app_root;
    $this->config = &$config;
    $this->databases = &$databases;
    $this->settings = &$settings;

    // Handle HTTPS
    if (getenv('HTTPS') !== 'on' && getenv('HTTP_X_FORWARDED_PROTO') === 'https') {
      $_SERVER['HTTPS'] = 'on';
      $_SERVER["SERVER_PORT"] = getenv('HTTP_X_FORWARDED_PORT') ?: 443;
    }

    // Detect system
    foreach (self::MAP as $env_key => $class) {
      if (getenv($env_key)) {
        $this->system = new $class();
        // Break on first detected.
        break;
      }
    }

    // Set mapped env variables IF we have detected something
    if ($this->system) {
      foreach ($this->system->getEnvs() as $env_var => $env_val) {
        putenv(sprintf('%s=%s', $env_var, $env_val));
      }

      // Set system specific configuration
      $this->system->setConfiguration($config, $settings);
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

    $features = new Features();

    $settings_dir = $app_root . DIRECTORY_SEPARATOR . $site_path;

    // Load/add files (if exist) from sites/default in following order:
    foreach (['all', $this->app_env, 'local'] as $set) {
      // all.settings.php, dev.settings.php and local.settings.php
      if (file_exists($settings_dir . DIRECTORY_SEPARATOR . $set . '.settings.php')) {
        include $settings_dir . DIRECTORY_SEPARATOR . $set . '.settings.php';
      }

      // all.services.yml, dev.services.yml and local.services.yml
      if (file_exists($settings_dir . DIRECTORY_SEPARATOR . $set . '.services.yml')) {
        $settings['container_yamls'][] = $settings_dir . DIRECTORY_SEPARATOR . $set . '.services.yml';
      }
    }

    // Set directory for loading CMI configuration.
    $this->setSetting('config_sync_directory');

    // Hash salt.
    $this->setSetting('hash_salt');

    // Set file paths.
    $this->setSetting('file_public_path');
    $this->setSetting('file_private_path');
    $this->setSetting('file_temp_path');

    $this->setTrustedHostPatterns();
    $this->setDatabaseConnection();
  }

  public static function get(array $vars): array
  {
    return (new Reader($vars))->getConf();
  }

  /**
   * Get read configuration.
   *
   * @return array
   */
  public function getConf(): array
  {
    $conf = [
      'config' => $this->config,
      'databases' => $this->databases,
      'settings' => $this->settings,
    ];

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
  public static function show(array $vars): void
  {
    $reader = new Reader($vars);
    $reader->printConfiguration($reader->getConf());
  }

  protected function printConfiguration($conf): void
  {
    $omen = $this->system ?? '[NOT_ANY_DETECTED_SYSTEM]';

    echo sprintf('<h1>Drupal: %s, APP_ENV: %s on %s</h1>', $this->getDrupalVersion(), $this->app_env, $omen);
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
  private function setEnvDefaults(): void
  {
    $env_defaults = (new Defaults($this->app_env))->getDefaults();

    foreach ($env_defaults as $set => $values) {
      if (!is_array($this->{$set})) {
        $this->{$set} = [];
      }

      $this->{$set} = array_merge($this->{$set}, $values);
    }
  }

  /**
   * Set trusted host patterns.
   *
   * @see https://www.drupal.org/node/2410395
   */
  private function setTrustedHostPatterns(): void
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

    if ($this->system && method_exists($this->system, 'getTrustedHostPatterns')) {
      $patterns = $this->system->getTrustedHostPatterns();
      $this->settings['trusted_host_patterns'] = array_merge($this->settings['trusted_host_patterns'], $patterns);
    }
  }

  /**
   * Set database connection.
   */
  private function setDatabaseConnection(): void
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
      'init_commands' => [
        'isolation_level' => 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
      ],
    ];
  }

  private function setSetting(string $setting): void
  {
    $env = sprintf('DRUPAL_%s', strtoupper($setting));
    $default = self::DRUPAL_SETTING_DEFAULTS[$setting];

    // Order of getting the value:
    // 1. ENV variable
    // 2. If variable was already set in $settings
    // 3. Default value
    $this->settings[$setting] = getenv($env) ?: $this->settings[$setting] ?? $default;
  }

  private function getDrupalVersion(): string
  {
    if (!$this->drupal_version) {
      // Detect Drupal version.
      $this->drupal_version = (new ReflectionClass('Drupal'))->getConstants()['VERSION'];
    }

    return $this->drupal_version;
  }
}
