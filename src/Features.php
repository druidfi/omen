<?php

namespace Druidfi\Omen;

class Features
{
  public bool $redis = false;
  public bool $solr = false;

  private array $redisEnvVariables = [
    'REDIS_HOST', // Lagoon
  ];

  private array $solrEnvVariables = [
    'SOLR_HOST', // Lagoon
  ];

  public function __construct()
  {
    $this->detectRedis();
    $this->detectSolr();
  }

  public function hasRedis(): bool
  {
    return $this->redis;
  }

  public function hasSolr(): bool
  {
    return $this->solr;
  }

  private function detectRedis(): void
  {
    foreach ($this->redisEnvVariables as $env) {
      if (getenv($env)) {
        $this->redis = true;
        break;
      }
    }
  }

  private function detectSolr(): void
  {
    foreach ($this->solrEnvVariables as $env) {
      if (getenv($env)) {
        $this->solr = true;
        break;
      }
    }
  }
}
