<?php

namespace Druidfi\Omen\System;

interface SystemInterface
{
  public function getAppEnv(): string;

  public function getEnvs(): array;
}
