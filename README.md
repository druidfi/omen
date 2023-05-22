# druidfi/omen

![Tests](https://github.com/druidfi/omen/workflows/Tests/badge.svg)

Drupal ENV detector. Detects env related configuration and sets them for you. Helps with moving sites from environment
to another. Aims also to generalize your env configuration.

Also sets configuration per environment type. e.g. for development or production. Everything can still be overridden in
your project.

**You should just focus on your project specific configuration.**

## How to use

Require omen in your composer.json:

```shell
composer require druidfi/omen
```

And then use this as your `sites/default/settings.php`:

```php
<?php

// Use druidfi/omen
extract(Druidfi\Omen\Reader::get(get_defined_vars()));
```

Or print out all configuration (aka debug):

```php
<?php

// Print out detected configuration by druidfi/omen
Druidfi\Omen\Reader::show(get_defined_vars());
```

See the whole example [here](settings.php).

## Known environments

- [Amazee.io Lagoon](https://docs.lagoon.sh/)
- [DDEV Local](https://ddev.readthedocs.io/en/latest/)
- [Lando](https://lando.dev/)
- [Pantheon](https://pantheon.io/) - Work in Progress
- [Tugboat](https://www.tugboat.qa/)
- [Wodby](https://wodby.com/)

## What is detected?

- Loading of setting files and service configurations
- Database connection
- Trusted host pattern(s)
- File paths (public, private, temp)
- Hash salt
- Contrib module settings (which are affected by env)
  - [Simple Environment Indicator](https://www.drupal.org/project/simplei)

## APP_ENV

With `APP_ENV` you can force a running configuration. E.g. you can run with `test` configuration on `dev` environment.
This means that e.g. the database credentials do not change but caching settings do change.

Values: `dev`, `test` or `prod` (default: `prod`)

## Drupal configuration mapping

Drupal configuration can be overridden using ENV variables.

| Variable                                       | ENV override               | Default value            |
|------------------------------------------------|----------------------------|--------------------------|
| `$databases['default']['default']['database']` | `DRUPAL_DB_NAME`           | :heavy_multiplication_x: |
| `$databases['default']['default']['driver']`   | `DRUPAL_DB_DRIVER`         | `'mysql'`                |
| `$databases['default']['default']['host']`     | `DRUPAL_DB_HOST`           | :heavy_multiplication_x: |
| `$databases['default']['default']['password']` | `DRUPAL_DB_PASS`           | :heavy_multiplication_x: |
| `$databases['default']['default']['port']`     | `DRUPAL_DB_PORT`           | `3306`                   |
| `$databases['default']['default']['username']` | `DRUPAL_DB_USER`           | :heavy_multiplication_x: |
| `$settings['file_public_path']`                | `DRUPAL_FILE_PUBLIC_PATH`  | `'sites/default/files'`  |
| `$settings['file_private_path']`               | `DRUPAL_FILE_PRIVATE_PATH` | `FALSE`                  |
| `$settings['file_temp_path']`                  | `DRUPAL_FILE_TEMP_PATH`    | `'/tmp'`                 |
| `$settings['hash_salt']`                       | `DRUPAL_HASH_SALT`         | `'0000000000000000'`     |

:heavy_multiplication_x: Detected or required

## Defaults for environment types

See [src/Defaults.php](src/Defaults.php) for values.

See current default values by environment:

| Variable                                                    | Development | Testing  | Production |
|-------------------------------------------------------------|-------------|----------|------------|
| `$config['system.logging']['error_level']`                  | `'all'`     | `'hide'` | `'hide'`   |
| `$config['system.performance']['cache']['page']['max_age']` | `0`         | `900`    | `900`      |
| `$config['system.performance']['css']['preprocess']`        | `0`         | `1`      | `1`        |
| `$config['system.performance']['js']['preprocess']`         | `0`         | `1`      | `1`        |
| `$settings['skip_permissions_hardening']`                   | `TRUE`      | `FALSE`  | `FALSE`    |

Same for all environments:

- `$settings['config_exclude_modules']` = `['devel','stage_file_proxy','upgrade_status']`
- `$settings['config_sync_directory']` = `'conf/cmi'`

## TODO

Add support for:

- Detect e.g. Solr, Redis and Varnish configuration where available
- Other dev tools and hosting environments
- Default values for some contrib modules

## Where the name "Omen" comes from?

Druids interpreted the waves of the ocean or read clouds for mundane or important omens. So `reading clouds` is
basically what `druidfi/omen` is doing. Your local clouds too.

## Other information

This project can be found from the Packagist: https://packagist.org/packages/druidfi/omen
