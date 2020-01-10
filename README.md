# druidfi/omen [Work in Progress]

[![Build Status](https://travis-ci.com/druidfi/omen.svg?branch=master)](https://travis-ci.com/druidfi/omen)

Drupal ENV detector. Detects env related configuration and sets them for you. Helps with moving sites from environment
to another. Aims also to generalize your env configuration.

You should just focus on your project specific configuration.

## How to use

Require omen in your composer.json:

```
$ composer require druidfi/omen:^1.0
```

And then use this as your `sites/default/settings.php`:

```
<?php

// Use druidfi/omen
extract((new Druidfi\Omen\DrupalEnvDetector(__DIR__))->getConfiguration());
```

See the whole example [here](settings.php).

## Known environments

- [Amazee.io Lagoon](https://lagoon.readthedocs.io/)
- [Amazee.io Legacy](https://docs.amazee.io/)
- [Lando](https://lando.dev/)
- [Wodby](https://wodby.com/)

## All other environments

Use following ENV variables to set up Drupal:

- `APP_ENV` as current environment, e.g. `dev`, `test` or `prod` (default: `prod`)
- `DRUPAL_DB_DRIVER` as database driver (default: `mysql`)
- `DRUPAL_DB_NAME` as database name *
- `DRUPAL_DB_HOST` as database host to connect *
- `DRUPAL_DB_USER` as database user for connection *
- `DRUPAL_DB_PASS` as database password for connection *
- `DRUPAL_DB_PORT` as database port (default: `3306`)
- `DRUPAL_DB_PREFIX` as database table prefix (default is no prefix)

`*` required variables

## TODO

Add support for:

- Lando
- Drupal VM
- Other dev tools and environments

## Where the name "Omen" comes from?

Druids interpreted the waves of the ocean or read clouds for mundane or important omens. So `reading clouds` is
basically what `druidfi/omen` is doing. Your local clouds too.

## Other information

This project is found from the Packagist: https://packagist.org/packages/druidfi/omen
