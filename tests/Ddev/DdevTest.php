<?php

namespace Druidfi\Omen\Tests;

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
}
