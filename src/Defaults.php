<?php

namespace Druidfi\Omen;

class Defaults
{
  private array $config = [];

  private array $settings = [
    'config_exclude_modules' => [
      'devel',
      'stage_file_proxy',
      'upgrade_status',
    ],
  ];

  public function __construct(string $app_env)
  {
    $this->config['system.logging']['error_level'] = match ($app_env) {
      'dev' => 'verbose',
      default => 'hide',
    };

    $this->config['system.performance'] = match ($app_env) {
      'dev' => [
        'cache' => ['page' => ['max_age' => 0]],
        'css' => ['preprocess' => 0],
        'js' => ['preprocess' => 0],
      ],
      default => [
        'cache' => ['page' => ['max_age' => 900]],
        'css' => ['preprocess' => 1],
        'js' => ['preprocess' => 1],
      ],
    };

    $this->settings['skip_permissions_hardening'] = match ($app_env) {
      'dev' => TRUE,
      default => FALSE,
    };

    // Simple Environment Indicator.
    $this->settings['simple_environment_indicator'] = match ($app_env) {
      'dev' => 'Black Development',
      'test' => 'Blue Testing',
      'prod' => 'DarkRed Production',
      default => FALSE,
    };
  }

  public function getDefaults() : array
  {
    return [
      'config' => $this->config,
      'settings' => $this->settings,
    ];
  }
}
