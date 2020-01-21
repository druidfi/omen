<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

use Druidfi\Omen\DrupalEnvDetector;

abstract class EnvMappingAbstract implements EnvMappingInterface {
  public function getAppEnv() {
    return DrupalEnvDetector::ENV_DEVELOPMENT;
  }

  public function getEnvs() : array {
    return [
      'APP_ENV' => $this->getAppEnv(),
    ];
  }
}
