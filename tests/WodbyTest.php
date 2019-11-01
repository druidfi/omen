<?php

use PHPUnit\Framework\TestCase;

class WodbyTest extends TestCase
{
    protected $values = [
        'name' => 'phpunit_db',
        'user' => 'phpunit_user',
        'pass' => 'phpunit_pass',
        'host' => 'phpunit_host',
        'port' => 'phpunit_port',
        'trusted_host_pattern' => 'site.wodby.com',
    ];

    protected function setUp(): void
    {
        putenv('WODBY_INSTANCE_TYPE=dev');
        putenv('WODBY_HOST_PRIMARY=' . $this->values['trusted_host_pattern']);

        putenv('DB_NAME=' . $this->values['name']);
        putenv('DB_USER=' . $this->values['user']);
        putenv('DB_PASSWORD=' . $this->values['pass']);
        putenv('DB_HOST=' . $this->values['host']);
        putenv('MARIADB_SERVICE_PORT=' . $this->values['port']);
    }

    public function testSettings()
    {
        /** @var array $settings */
        /** @var array $databases */
        extract((new Druidfi\Omen\DrupalEnvDetector(__DIR__))->getConfiguration());

        // Test trusted host pattern
        $pattern = '^' . str_replace('.', '\.', $this->values['trusted_host_pattern']) . '$';
        $this->assertSame($pattern, $settings['trusted_host_patterns'][0]);

        // Test database settings
        $db = $databases['default']['default'];
        $this->assertSame($this->values['name'], $db['database']);
        $this->assertSame($this->values['user'], $db['username']);
        $this->assertSame($this->values['pass'], $db['password']);
        $this->assertSame($this->values['host'], $db['host']);
        $this->assertSame($this->values['port'], $db['port']);
    }
}
