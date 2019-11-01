<?php

use PHPUnit\Framework\TestCase;

class LagoonTest extends TestCase
{
    protected $values = [
        'name' => 'phpunit_db',
        'user' => 'phpunit_user',
        'pass' => 'phpunit_pass',
        'host' => 'phpunit_host',
        'port' => 'phpunit_port',
        'trusted_host_pattern' => 'site.lagoon.io',
    ];

    protected function setUp(): void
    {
        putenv('LAGOON=lagoon');
        putenv('LAGOON_ENVIRONMENT_TYPE=development');
        putenv('HOSTNAME=' . $this->values['trusted_host_pattern']);

        putenv('MARIADB_DATABASE=' . $this->values['name']);
        putenv('MARIADB_USERNAME=' . $this->values['user']);
        putenv('MARIADB_PASSWORD=' . $this->values['pass']);
        putenv('MARIADB_HOST=' . $this->values['host']);
        //putenv('AMAZEEIO_DB_PORT=' . $this->values['port']);
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
        $this->assertSame($this->values['name'], $db['database'], print_r($db, TRUE));
        $this->assertSame($this->values['user'], $db['username']);
        $this->assertSame($this->values['pass'], $db['password']);
        $this->assertSame($this->values['host'], $db['host']);
        //$this->assertSame($this->values['port'], $db['port']);
    }
}
