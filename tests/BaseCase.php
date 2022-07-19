<?php

namespace Druidfi\Omen\Tests;

use Druidfi\Omen\Reader;
use PHPUnit\Framework\TestCase;

abstract class BaseCase extends TestCase
{
  protected array $expected_db_settings = [
    'driver' => 'mysql',
    'name' => 'drupal',
    'user' => 'drupal',
    'pass' => 'drupal',
    'host' => 'db',
    'port' => '3306',
    'prefix' => '',
  ];

  protected string $expected_host = 'local.drupal.com';

  protected ?string $expected_hash_salt = "hash";

  protected array $config = [];

  protected array $databases = [];

  protected array $settings = [];

  protected function setUp(): void
  {
    if (!class_exists('Drupal')) {
      eval("class Drupal { const VERSION = '9.4.0'; }");
    }

    $conf = Reader::get(['app_root' => '/app/public', 'site_path' => 'site/default']);

    /** @var array $config */
    /** @var array $settings */
    /** @var array $databases */
    extract($conf);

    //echo json_encode($conf, JSON_PRETTY_PRINT);

    $this->config = $config;
    $this->databases = $databases;
    $this->settings = $settings;
  }

  /**
   * Test database settings.
   */
  public function testDbSettings()
  {
    $db = $this->databases['default']['default'];

    // Test database settings
    $this->assertEquals($this->expected_db_settings['driver'], $db['driver']);
    $this->assertEquals($this->expected_db_settings['name'], $db['database']);
    $this->assertEquals($this->expected_db_settings['user'], $db['username']);
    $this->assertEquals($this->expected_db_settings['pass'], $db['password']);
    $this->assertEquals($this->expected_db_settings['host'], $db['host']);
    $this->assertEquals($this->expected_db_settings['port'], $db['port']);
    $this->assertEquals($this->expected_db_settings['prefix'], $db['prefix']);
  }

  public function testTrustedHostPattern()
  {
    // Test trusted host pattern
    $pattern = '^' . str_replace('.', '\.', $this->expected_host) . '$';
    $message = print_r($this->settings['trusted_host_patterns'], true);
    $this->assertContains($pattern, $this->settings['trusted_host_patterns'], $message);
  }

  public function testHash()
  {
    if ($this->expected_hash_salt) {
      $this->assertEquals($this->expected_hash_salt, $this->settings['hash_salt']);
    }
    // Do not test if explicitly set to null in the test class.
    else {
      $this->assertTrue(TRUE);
    }
  }

  public function testConfigDefaults()
  {
    $app_env = getenv('APP_ENV');

    $error_level = $this->config['system.logging']['error_level'];
    $expect = ($app_env === Reader::ENV_DEVELOPMENT) ? 'all' : 'hide';
    $this->assertEquals($expect, $error_level);

    $preprocess_css = $this->config['system.performance']['css']['preprocess'];
    $expect = ($app_env === Reader::ENV_DEVELOPMENT) ? 0 : 1;
    $this->assertEquals($expect, $preprocess_css);

    $preprocess_js = $this->config['system.performance']['js']['preprocess'];
    $expect = ($app_env === Reader::ENV_DEVELOPMENT) ? 0 : 1;
    $this->assertEquals($expect, $preprocess_js);
  }

  public function testSimpleEnvironmentIndicator()
  {
    $app_env = getenv('APP_ENV');
    $value = $this->settings['simple_environment_indicator'] ?: FALSE;
    $expect = [
      'dev' => 'Black Development',
      'test' => 'Blue Testing',
      'prod' => 'DarkRed Production',
    ];
    $this->assertEquals($expect[$app_env], $value);
  }
}
