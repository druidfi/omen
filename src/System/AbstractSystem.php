<?php

namespace Druidfi\Omen\System;

use Druidfi\Omen\Reader;

abstract class AbstractSystem implements SystemInterface
{
  protected string $env_name = 'LOCAL_ENV_TYPE';
  protected array $env_type_map = [];

  public function getAppEnv(): string
  {
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

    return Reader::ENV_DEVELOPMENT;
  }

  public function setConfiguration(&$config, &$settings): void
  {
  }

  public function getEnvs(): array
  {
    return [
      'APP_ENV' => $this->getAppEnv(),
    ];
  }
}
