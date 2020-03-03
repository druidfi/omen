<?php

namespace Druidfi\Omen\Tests;

class DdevTest extends BaseCase
{
  protected $expected_db_settings = [
    'driver' => 'mysql',
    'name' => 'db',
    'user' => 'db',
    'pass' => 'db',
    'host' => 'db',
    'port' => '3306',
    'prefix' => '',
  ];

  protected $expected_host = 'local.ddev.site';

  protected $expected_hash_salt = 'xjfCkJoDvrzrDHbiabQAvHMYtMltJUrgyinZfDuCFiDrXMiqQCfpRWouLfkAwdVr';

  protected function setUp(): void
  {
    parent::setUp();

    $this->settings['hash_salt'] = 'xjfCkJoDvrzrDHbiabQAvHMYtMltJUrgyinZfDuCFiDrXMiqQCfpRWouLfkAwdVr';
  }
}
