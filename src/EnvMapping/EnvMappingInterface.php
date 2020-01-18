<?php

namespace Druidfi\Omen\EnvMapping;

interface EnvMappingInterface {
  public function getAppEnv();

  public function getEnvs() : array;
}
