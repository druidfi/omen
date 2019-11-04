<?php


namespace Druidfi\Omen\Tests;

use Druidfi\Omen\DrupalEnvDetector;
use PHPUnit\Framework\TestCase;

abstract class BaseCase extends TestCase
{
    protected $expected_db_settings = [
        'name' => 'drupal',
        'user' => 'drupal',
        'pass' => 'drupal',
        'host' => 'db',
        'port' => '3306',
    ];

    protected $expected_host = 'local.drupal.com';

    protected $databases = [];

    protected $settings = [];

    protected function setUp(): void
    {
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
        $this->assertEquals($this->expected_db_settings['name'], $db['database']);
        $this->assertEquals($this->expected_db_settings['user'], $db['username']);
        $this->assertEquals($this->expected_db_settings['pass'], $db['password']);
        $this->assertEquals($this->expected_db_settings['host'], $db['host']);
        $this->assertEquals($this->expected_db_settings['port'], $db['port']);
    }

    public function testTrustedHostPattern()
    {
        // Test trusted host pattern
        $pattern = '^' . str_replace('.', '\.', $this->expected_host) . '$';
        $this->assertEquals($pattern, $this->settings['trusted_host_patterns'][0]);
    }
}
