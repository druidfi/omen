<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

abstract class EnvMappingAbstract implements EnvMappingInterface {
  public function getAppEnv() {
    return 'dev';
  }

  public function getEnvs() : array {
    return [
      'APP_ENV' => $this->getAppEnv(),
    ];
  }
}
