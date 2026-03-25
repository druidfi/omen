<?php

namespace Druidfi\Omen\Tests;

class UpsunTest extends BaseCase
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

  // Primary upstream — ends up as DRUSH_OPTIONS_URI, trusted via that path.
  protected string $expected_host = 'main.drupal.upsun.app';

  protected ?string $expected_hash_salt = 'upsun-project-entropy-value';

  public function testProxySettings()
  {
    $this->assertTrue($this->settings['reverse_proxy']);
  }

  public function testDeploymentIdentifier()
  {
    $this->assertEquals('abc123treeid', $this->settings['deployment_identifier']);
  }

  public function testPhpStoragePaths()
  {
    $this->assertStringContainsString('/private', $this->settings['php_storage']['default']['directory']);
    $this->assertStringContainsString('/private', $this->settings['php_storage']['twig']['directory']);
  }

  public function testPrimaryRouteIsUsedAsDrushUri()
  {
    $this->assertStringContainsString('main.drupal.upsun.app', getenv('DRUSH_OPTIONS_URI'));
  }

  public function testSecondaryUpstreamIsInTrustedHosts()
  {
    // Non-primary upstream routes go to DRUPAL_ROUTES and end up in trusted_host_patterns.
    $pattern = '^secondary\.drupal\.upsun\.app$';
    $this->assertContains($pattern, $this->settings['trusted_host_patterns']);
  }

  public function testRedirectRoutesExcludedFromTrustedHosts()
  {
    // Redirect-type routes must not appear in trusted_host_patterns.
    foreach ($this->settings['trusted_host_patterns'] as $pattern) {
      $this->assertStringNotContainsString('www\.main\.drupal\.upsun\.app', $pattern);
    }
  }
}
