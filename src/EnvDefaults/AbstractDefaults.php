<?php

namespace Druidfi\Omen\EnvDefaults;

abstract class AbstractDefaults {

  protected array $config = [];
  protected array $settings = [];

  public function getDefaults() : array {
    return [
      'config' => $this->config,
      'settings' => $this->settings,
    ];
  }
}
