<?php

namespace Druidfi\Omen\Tests;

use Druidfi\Omen\DrupalEnvDetector;
use PHPUnit\Framework\TestCase;

abstract class BaseCase extends TestCase
{
  protected $expected_db_settings = [
    'driver' => 'mysql',
    'name' => 'drupal',
    'user' => 'drupal',
    'pass' => 'drupal',
    'host' => 'db',
    'port' => '3306',
    'prefix' => '',
  ];

  protected $expected_host = 'local.drupal.com';

  protected $databases = [];

  protected $settings = [];

  protected function setUp(): void
  {
    if (!class_exists('Drupal')) {
      eval("class Drupal { const VERSION = '8.8.1'; }");
    }

    /** @var array $settings */
    /** @var array $databases */
    extract((new DrupalEnvDetector(__DIR__))->getConfiguration());

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
    $this->assertEquals($pattern, $this->settings['trusted_host_patterns'][0]);
  }
}
