<?php

namespace Druidfi\Omen\EnvDefaults;

class ProdDefaults extends AbstractDefaults {

  protected array $config = [
    'stage_file_proxy.settings' => [
      'origin' => FALSE,
    ],
    'system.logging' => [
      'error_level' => 'hide'
    ],
    'system.performance' => [
      'cache' => [
        'page' => [
          'max_age' => 900,
        ]
      ],
      'css' => [
        'preprocess' => 1,
      ],
      'js' => [
        'preprocess' => 1,
      ],
    ],
  ];

  protected array $settings = [
    'simple_environment_indicator' => 'DarkRed Production', // Simple Environment Indicator.
    'skip_permissions_hardening' => FALSE,
  ];
}
