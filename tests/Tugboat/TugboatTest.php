<?php

namespace Druidfi\Omen\Tests;

class TugboatTest extends BaseCase
{
  protected array $expected_db_settings = [
    'driver' => 'mysql',
    'name' => 'tugboat',
    'user' => 'tugboat',
    'pass' => 'tugboat',
    'host' => 'mysql',
    'port' => '3306',
    'prefix' => '',
  ];

  protected string $expected_host = 'pr284-elxdkz217euqq2drrbrupuuulkastan0.tugboat.qa';

  protected ?string $expected_hash_salt = NULL;
}
