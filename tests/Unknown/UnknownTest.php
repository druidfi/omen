<?php

namespace Druidfi\Omen\Tests;

use Druidfi\Omen\Reader;

class UnknownTest extends BaseCase
{
  protected string $expected_host = 'dev.drupal.com';

  protected ?string $expected_hash_salt = NULL;

  protected function setUp(): void
  {
    if (!class_exists('Drupal')) {
      eval("class Drupal { const VERSION = '10.0.0'; }");
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

  public function testEject(): void
  {
    $dir = sys_get_temp_dir() . '/omen-eject-unknown-' . uniqid();
    mkdir($dir, 0777, true);

    try {
      $expected = Reader::get(['app_root' => $dir, 'site_path' => '.']);
      $actual = Reader::eject(['app_root' => $dir, 'site_path' => '.']);

      $this->assertEquals($expected, $actual);

      $this->assertFileExists($dir . '/settings.ejected.php');
      $code = file_get_contents($dir . '/settings.ejected.php');
      $this->assertStringContainsString("getenv('DRUPAL_DB_NAME')", $code);
      $this->assertStringContainsString('Detected system: none detected', $code);

      // The Defaults-derived match() blocks branch on all three envs,
      // regardless of which APP_ENV was active when eject() ran.
      $this->assertStringContainsString("'dev' => 'Black Development'", $code);
      $this->assertStringContainsString("'test' => 'Blue Testing'", $code);
      $this->assertStringContainsString("default => 'DarkRed Production'", $code);

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
