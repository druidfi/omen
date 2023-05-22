<?php

namespace Druidfi\Omen\EnvMapping;

interface EnvMappingInterface
{
  public function getAppEnv(): string;

  public function getEnvs(): array;
}
