<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvDefaults;

class ProdDefaults extends AbstractDefaults {
  public function __construct()
  {
    // Don't show any error messages on the site (will still be shown in watchdog)
    $config['system.logging']['error_level'] = 'hide';

    // Expiration of cached pages on Varnish to 15 min.
    $config['system.performance']['cache']['page']['max_age'] = 900;

    // Aggregate CSS files on.
    $config['system.performance']['css']['preprocess'] = 1;

    // Aggregate JavaScript files on.
    $config['system.performance']['js']['preprocess'] = 1;

    $this->config = $config;
  }

  protected function getContribModuleDefaults() : array {
    // Simple Environment Indicator.
    $settings['simple_environment_indicator'] = 'DarkRed Production';

    return [
      'config' => [],
      'settings' => $settings,
    ];
  }
}
