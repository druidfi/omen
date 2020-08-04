<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

use Druidfi\Omen\DrupalEnvDetector;

abstract class EnvMappingAbstract implements EnvMappingInterface {

  protected $env_name = 'LOCAL_ENV_TYPE';
  protected $env_type_map = [];

  public function getAppEnv() {
    if (getenv('APP_ENV')) {
      return getenv('APP_ENV');
    }

    if (getenv($this->env_name)) {
      foreach ($this->env_type_map as $source => $target) {
        if (getenv($this->env_name) === $source) {
          return $target;
        }
      }

      return getenv($this->env_name);
    }

    return DrupalEnvDetector::ENV_DEVELOPMENT;
  }

  public function setConfiguration(&$config, &$settings) {
  }

  public function getEnvs() : array {
    return [
      'APP_ENV' => $this->getAppEnv(),
    ];
  }
}
