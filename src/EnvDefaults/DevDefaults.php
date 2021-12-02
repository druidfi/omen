<?php

namespace Druidfi\Omen\EnvDefaults;

class DevDefaults extends AbstractDefaults {

  protected array $config = [
    'stage_file_proxy.settings' => [
      'origin' => FALSE,
    ],
    'system.logging' => [
      'error_level' => 'all'
    ],
    'system.performance' => [
      'cache' => [
        'page' => [
          'max_age' => 0,
        ]
      ],
      'css' => [
        'preprocess' => 0,
      ],
      'js' => [
        'preprocess' => 0,
      ],
    ],
  ];

  protected array $settings = [
    'simple_environment_indicator' => 'Black Development', // Simple Environment Indicator.
    'skip_permissions_hardening' => FALSE,
  ];
}
