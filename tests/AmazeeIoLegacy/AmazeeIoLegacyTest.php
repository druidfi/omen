<?php

namespace Druidfi\Omen\Tests;

class AmazeeIoLegacyTest extends BaseCase
{
  protected $expected_host = 'mysite.com.develop.zh1.compact.amazee.io';

  public function testProxySettings()
  {
    $this->assertEquals(TRUE, $this->settings['reverse_proxy']);
  }
}
