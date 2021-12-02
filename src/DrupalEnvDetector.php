<?php

namespace Druidfi\Omen;

class DrupalEnvDetector
{
  public function __construct(string $settings_dir)
  {
    echo "<h1>BC BREAK IN OMEN!</h1>";
    echo "<p>Update your call in settings.php in ". $settings_dir ."</p>";
    echo "<p><strong>Old line:</strong>";
    echo "<p><code>extract((new Druidfi\Omen\DrupalEnvDetector(__DIR__))->getConfiguration());</code>";
    echo "<p><strong>New line:</strong>";
    echo "<p><code>extract((new Druidfi\Omen\Reader(__DIR__))->get());</code>";
    exit;
  }
}
