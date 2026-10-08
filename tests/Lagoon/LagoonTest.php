<?php

namespace Druidfi\Omen\Tests;

use Druidfi\Omen\Reader;

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
    $this->assertTrue($this->settings['reverse_proxy']);
  }

  public function testEject(): void
  {
    $dir = sys_get_temp_dir() . '/omen-eject-lagoon-' . uniqid();
    mkdir($dir, 0777, true);

    try {
      $expected = Reader::get(['app_root' => $dir, 'site_path' => '.']);
      $actual = Reader::eject(['app_root' => $dir, 'site_path' => '.']);

      $this->assertEquals($expected, $actual);

      $this->assertFileExists($dir . '/settings.ejected.php');
      $code = file_get_contents($dir . '/settings.ejected.php');
      $this->assertStringContainsString('MARIADB_DATABASE', $code);
      $this->assertStringContainsString('LAGOON_PROJECT', $code);

      $app_root = $dir;
      $site_path = '.';
      $config = [];
      $databases = [];
      $settings = [];
      require $dir . '/settings.ejected.php';

      $this->assertEquals($expected['config'], $config);
      $this->assertEquals($expected['databases'], $databases);
      $this->assertEquals($expected['settings'], $settings);
    }
    finally {
      @unlink($dir . '/settings.ejected.php');
      @rmdir($dir);
    }
  }
}
