<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvDefaults;

abstract class AbstractDefaults {
  protected array $config = [];
  protected array $settings = [];

  public function getDefaults() : array {
    $contrib_module_defaults = $this->getContribModuleDefaults();

    $config = array_merge($this->config, $contrib_module_defaults['config']);
    $settings = array_merge($this->settings, $contrib_module_defaults['settings']);

    return [
      'config' => $config,
      'settings' => $settings,
    ];
  }

  protected function getContribModuleDefaults() : array {
    return [
      'config' => [],
      'settings' => [],
    ];
  }
}
