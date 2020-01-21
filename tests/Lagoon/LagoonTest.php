<?php

namespace Druidfi\Omen\Tests;

class LagoonTest extends BaseCase
{
  protected $expected_db_settings = [
    'driver' => 'mysql',
    'name' => 'lagoon',
    'user' => 'lagoon',
    'pass' => 'lagoon',
    'host' => 'db',
    'port' => '3306',
    'prefix' => '',
  ];

  protected $expected_host = 'https://nginx-drupal-dev.ch.amazee.io';
}
