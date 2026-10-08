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
      $this->assertStringContainsString("'prod' => 'DarkRed Production'", $code);
      // Any other APP_ENV (e.g. 'stage') gets no indicator, as with Omen.
      $this->assertStringContainsString("default => false", $code);

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

  public function testEjectProjectFiles(): void
  {
    $dir = sys_get_temp_dir() . '/omen-eject-unknown-project-' . uniqid();
    mkdir($dir, 0777, true);

    $files = [
      'settings.php' => <<<'PHP'
<?php

use Druidfi\Omen\Reader;

$settings['file_private_path'] = 'before';

// Use druidfi/omen to auto-configure Drupal
//
// These files are loaded automatically if found.
extract(Reader::get(get_defined_vars()));

// Runs after Omen, so sees its resolved values.
$settings['after'] = $settings['file_private_path'];
PHP,
      'all.settings.php' => <<<'PHP'
<?php
use Pdo\Mysql;

$databases['default']['default']['pdo'][1012] = '/ca.pem';
PHP,
      'dev.settings.php' => "<?php\nuse Drupal\\Core\\Site\\Settings;\n\n\$settings['env_file'] = 'dev';\n",
      'test.settings.php' => "<?php\nuse Drupal\\Core\\Site\\Settings;\n\n\$settings['env_file'] = 'test';\n",
      'prod.settings.php' => "<?php\n\$settings['env_file'] = 'prod';\n",
      'dev.services.yml' => "parameters: {}\n",
      'prod.services.yml' => "parameters: {}\n",
    ];

    foreach ($files as $name => $contents) {
      file_put_contents($dir . '/' . $name, $contents);
    }

    try {
      $vars = ['app_root' => $dir, 'site_path' => '.', 'settings' => ['file_private_path' => 'before']];
      $expected = Reader::get($vars);
      $expected['settings']['after'] = $expected['settings']['file_private_path'];

      Reader::eject($vars);
      $code = file_get_contents($dir . '/settings.ejected.php');

      // Omen's boilerplate comment and import are dropped, the rest carried.
      $this->assertStringNotContainsString('auto-configure', $code);
      $this->assertStringNotContainsString('Druidfi\Omen\Reader;', $code);
      $this->assertStringNotContainsString('extract(Reader::get(', $code);
      $this->assertLessThan(strpos($code, '$routes = [];'), strpos($code, "\$settings['file_private_path'] = 'before';"));
      $this->assertGreaterThan(strpos($code, "\$settings['enable_html5_validation']") ?: strpos($code, "\$settings['state_cache']"), strpos($code, "\$settings['after']"));

      // `use` imports are hoisted and deduplicated.
      $this->assertSame(1, substr_count($code, 'use Drupal\Core\Site\Settings;'));
      $this->assertMatchesRegularExpression('/^use Pdo\\\\Mysql;$/m', $code);

      // Env-specific services are gated on $app_env.
      $this->assertStringContainsString("if (\$app_env === 'dev') {\n  \$settings['container_yamls'][] = \$app_root . '/' . \$site_path . '/dev.services.yml';", $code);

      $this->assertStringContainsString('had NO effect', $code);

      $app_root = $dir;
      $site_path = '.';
      $config = [];
      $databases = [];
      $settings = [];
      require $dir . '/settings.ejected.php';

      // The one intended difference: the project's $databases changes now
      // apply instead of being overwritten by Reader::setDatabaseConnection().
      $this->assertSame('/ca.pem', $databases['default']['default']['pdo'][1012]);
      unset($databases['default']['default']['pdo']);

      $this->assertEquals($expected['config'], $config);
      $this->assertEquals($expected['databases'], $databases);
      $this->assertEquals($expected['settings'], $settings);
    }
    finally {
      foreach (array_keys($files) as $name) {
        @unlink($dir . '/' . $name);
      }
      @unlink($dir . '/settings.ejected.php');
      @rmdir($dir);
    }
  }
}
