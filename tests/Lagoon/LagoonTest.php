<?php

namespace Druidfi\Omen\Tests;

class LagoonTest extends BaseCase
{
  protected array $expected_db_settings = [
    'driver' => 'mysql',
    'name' => 'lagoon',
    'user' => 'lagoon',
    'pass' => 'lagoon',
    'host' => 'db',
    'port' => '3306',
    'prefix' => '',
  ];

  protected string $expected_host = 'nginx-drupal-dev.ch.amazee.io';

  protected ?string $expected_hash_salt = '1a16b869ad6213440f9466338d3066fdf7d5addab8855fc3cc9928258ccbbeb0';

  public function testHttps()
  {
    $value = $_SERVER['HTTP_X_FORWARDED_PROTO'];
    $expect = 'https';
    $this->assertEquals($expect, $value);
  }

  public function testProxySettings()
  {
    $this->assertEquals(TRUE, $this->settings['reverse_proxy']);
  }
}
