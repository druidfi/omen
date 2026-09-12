<?php

namespace Druidfi\Omen\System;

interface SystemInterface
{
  public function getAppEnv(): string;

  public function getEnvs(): array;

  /**
   * Static PHP source reproducing getEnvs()/setConfiguration() for this system,
   * for Reader::eject() to inline into a generated settings.php.
   *
   * The returned code (no <?php tag) must define $app_env, and may read/write
   * $config, $databases and $settings. $routes (array of route URLs),
   * $drush_options_uri and $system_trusted_host_patterns (array of extra
   * trusted host regexes) are pre-declared as empty by the caller and may be
   * overridden.
   */
  public function getEjectedCode(): string;
}
