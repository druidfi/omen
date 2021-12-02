<?php

namespace Druidfi\Omen\EnvDefaults;

abstract class AbstractDefaults
{
  protected array $config = [];
  protected array $settings = [];

  public function getDefaults() : array
  {
    return $this->alter([
      'config' => $this->config,
      'settings' => $this->settings,
    ]);
  }

  private function alter(array $defaults) : array
  {
    // Add defaults which are same always.

    // Exclude these modules from configuration export.
    $defaults['settings']['config_exclude_modules'] = [
      'devel',
      'stage_file_proxy',
      'upgrade_status',
    ];

    return $defaults;
  }
}
