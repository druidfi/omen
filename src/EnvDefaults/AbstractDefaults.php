<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvDefaults;

abstract class AbstractDefaults {
  protected $config = [];
  protected $settings = [];

  public function getDefaults() {
    return [
      'config' => $this->config,
      'settings' => $this->settings,
    ];
  }
}
