<?php

namespace Druidfi\Omen\Tests;

use Druidfi\Omen\Reader;

class DdevTest extends BaseCase
{
  protected array $expected_db_settings = [
    'driver' => 'mysql',
    'name' => 'db',
    'user' => 'db',
    'pass' => 'db',
    'host' => 'db',
    'port' => '3306',
    'prefix' => '',
  ];

  protected string $expected_host = 'local.ddev.site';

  protected ?string $expected_hash_salt = 'xjfCkJoDvrzrDHbiabQAvHMYtMltJUrgyinZfDuCFiDrXMiqQCfpRWouLfkAwdVr';

  protected function setUp(): void
  {
    parent::setUp();

    $this->settings['hash_salt'] = 'xjfCkJoDvrzrDHbiabQAvHMYtMltJUrgyinZfDuCFiDrXMiqQCfpRWouLfkAwdVr';
  }

  public function testEject(): void
  {
    $dir = sys_get_temp_dir() . '/omen-eject-ddev-' . uniqid();
    mkdir($dir, 0777, true);

    try {
      $expected = Reader::get(['app_root' => $dir, 'site_path' => '.']);
      $actual = Reader::eject(['app_root' => $dir, 'site_path' => '.']);

      // eject() is a safe drop-in for get() while the generated file is reviewed.
      $this->assertEquals($expected, $actual);

      $this->assertFileExists($dir . '/settings.ejected.php');
      $code = file_get_contents($dir . '/settings.ejected.php');
      $this->assertStringContainsString("getenv('VIRTUAL_HOST')", $code);

      // Executing the generated file must reproduce the same configuration.
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
