# druidfi/omen

[![Build Status](https://travis-ci.com/druidfi/omen.svg?branch=master)](https://travis-ci.com/druidfi/omen)

Drupal ENV detector. Detects env related configuration and sets them for you. Helps with moving sites from environment
to another. Aims also to generalize your env configuration.

Also sets configuration per environment type. e.g. for development or production. Everything can still be overriden in
your project.

**You should just focus on your project specific configuration.**

## How to use

Require omen in your composer.json:

```
$ composer require druidfi/omen
```

And then use this as your `sites/default/settings.php`:

```
<?php

// Use druidfi/omen
extract((new Druidfi\Omen\DrupalEnvDetector(__DIR__))->getConfiguration());
```

Or print out all configuration (aka debug):

```
<?php

// Print out detected configuration by druidfi/omen
(new Druidfi\Omen\DrupalEnvDetector(__DIR__))->showConfiguration();
```

See the whole example [here](settings.php).

## Known environments

- [Amazee.io Lagoon](https://lagoon.readthedocs.io/)
- [Amazee.io Legacy](https://docs.amazee.io/)
- [Lando](https://lando.dev/)
- [Pantheon](https://pantheon.io/)
- [Wodby](https://wodby.com/)

## What is detected?

- Database connection
- Trusted host pattern(s)
- File paths (public, private, tmp)
- Hash salt
- TODO: Solr, Redis

## APP_ENV

With `APP_ENV` you can force a running configuration. E.g. you can run with `test` configuration on `dev` environment.
This means that e.g. the database credentials do not change but caching settings do change.

Values: `dev`, `test` or `prod` (default: `prod`)

## Drupal configuration mapping

Drupal configuration can be overridden using ENV variables.

Variable | ENV override | Default value
--- | ------ | ---
`$config['system.file']['path']['temporary']` | `DRUPAL_TMP_PATH` | `'/tmp'`
`$databases['default']['default']['database']` | `DRUPAL_DB_NAME` | REQUIRED
`$databases['default']['default']['driver']` | `DRUPAL_DB_DRIVER` | `'mysql'`
`$databases['default']['default']['host']` | `DRUPAL_DB_HOST` | REQUIRED
`$databases['default']['default']['password']` | `DRUPAL_DB_PASS` | REQUIRED
`$databases['default']['default']['port']` | `DRUPAL_DB_PORT` | `3306`
`$databases['default']['default']['username']` | `DRUPAL_DB_USER` | REQUIRED
`$settings['hash_salt']` | `DRUPAL_HASH_SALT` | `'0000000000000000'`
`$settings['config_sync_directory']` | TODO | `'conf/cmi'`

## Defaults for environment types

- development: see [src/defaults/dev.php](src/EnvDefaults/dev.php)
- production: see [src/defaults/prod.php](src/EnvDefaults/prod.php)

See current default values by environment:

Variable | Development | Testing | Production
--- | ------ | ----------- | ---
`$config['system.logging']['error_level']` | `'all'` | `'hide'` | `'hide'`
`$config['system.performance']['cache']['page']['max_age']` | `0` | `900` | `900`
`$config['system.performance']['css']['preprocess']` | `0` | `1` | `1`
`$config['system.performance']['js']['preprocess']` | `0` | `1` | `1`
`$settings['skip_permissions_hardening']` | `TRUE` | `FALSE` | `FALSE`

## TODO

Add support for:

- Drupal VM
- Other dev tools and hosting environments

## Where the name "Omen" comes from?

Druids interpreted the waves of the ocean or read clouds for mundane or important omens. So `reading clouds` is
basically what `druidfi/omen` is doing. Your local clouds too.

## Other information

This project is found from the Packagist: https://packagist.org/packages/druidfi/omen
