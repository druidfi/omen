<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvDefaults;

class DevDefaults extends AbstractDefaults {
  public function __construct()
  {
    // Skip file system permissions hardening.
    $settings['skip_permissions_hardening'] = TRUE;

    // Show all error messages on the site.
    $config['system.logging']['error_level'] = 'all';

    // Expiration of cached pages to 0.
    $config['system.performance']['cache']['page']['max_age'] = 0;

    // Aggregate CSS files off.
    $config['system.performance']['css']['preprocess'] = 0;

    // Aggregate JavaScript files off.
    $config['system.performance']['js']['preprocess'] = 0;

    $this->config = $config;
    $this->settings = $settings;
  }

  protected function getContribModuleDefaults() : array {
    // Simple Environment Indicator.
    $settings['simple_environment_indicator'] = 'Black Development';

    return [
      'config' => [],
      'settings' => $settings,
    ];
  }
}
