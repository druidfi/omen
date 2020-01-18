<?php declare(strict_types=1);

namespace Druidfi\Omen\EnvMapping;

abstract class EnvMappingAbstract implements EnvMappingInterface {
  protected function getAppEnv() {
    return 'dev';
  }

  protected function getEnvs() : array {
    return [
      'APP_ENV' => $this->getAppEnv(),
    ];
  }

  public function getOmens() : array {
    return [
      'envs' => $this->getEnvs(),
    ];
  }
}
