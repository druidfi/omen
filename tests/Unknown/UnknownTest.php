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
}
