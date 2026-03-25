# Project: druidfi/omen

See `/CLAUDE.md` for Druid.fi platform standards. This file documents project-specific details only.

## What this project is

A PHP library that auto-detects the hosting environment for Drupal and injects `$databases`, `$settings`, and `$config` into `settings.php` so projects don't need environment-specific conditionals.

Entry point in Drupal projects:
```php
extract(Druidfi\Omen\Reader::get(get_defined_vars()));
```

## Project Structure

```
src/
  Reader.php           # Main class: detects system, applies defaults, sets all Drupal vars
  Defaults.php         # Per-env (dev/test/prod) $config and $settings defaults
  Features.php         # Redis/Solr detection (WIP, not yet wired into Reader)
  System/
    SystemInterface.php    # Interface: getEnvs(), setConfiguration(), getAppEnv()
    AbstractSystem.php     # Base: APP_ENV mapping, default getEnvs() returning APP_ENV
    Lagoon.php             # Amazee.io Lagoon
    Ddev.php               # DDEV Local
    Lando.php              # Lando
    Pantheon.php           # Pantheon (WIP)
    Tugboat.php            # Tugboat QA
    Wodby.php              # Wodby
tests/
  BaseCase.php             # PHPUnit base: setUp() calls Reader::get(), shared test methods
  {System}/
    {System}Test.php       # Per-system test, extends BaseCase, overrides expected values
  {System}.xml             # PHPUnit config, sets ENV vars to simulate that system
```

## Detection Order

`Reader::MAP` defines detection priority (first match wins):
1. `LAGOON` → Lagoon
2. `IS_DDEV_PROJECT` → Ddev
3. `LANDO_INFO` → Lando
4. `PANTHEON_ENVIRONMENT` → Pantheon
5. `TUGBOAT_PREVIEW_ID` → Tugboat
6. `PLATFORM_APPLICATION` → Upsun
7. `WODBY_INSTANCE_TYPE` → Wodby

If none match → "Unknown" environment (still sets DB from `DRUPAL_DB_*` env vars).

## APP_ENV

`APP_ENV` controls the running configuration type: `dev`, `test`, or `prod` (default: `prod`).

Each system maps its own env type variable to APP_ENV via `$env_type_map`. Can always be overridden by setting `APP_ENV` directly.

## DRUPAL_* Environment Variables

All Drupal settings can be overridden via ENV:

| Variable | ENV override | Default |
|---|---|---|
| `$databases[...][database]` | `DRUPAL_DB_NAME` | `drupal` |
| `$databases[...][driver]` | `DRUPAL_DB_DRIVER` | `mysql` |
| `$databases[...][host]` | `DRUPAL_DB_HOST` | `db` |
| `$databases[...][password]` | `DRUPAL_DB_PASS` | `drupal` |
| `$databases[...][port]` | `DRUPAL_DB_PORT` | `3306` |
| `$databases[...][username]` | `DRUPAL_DB_USER` | `drupal` |
| `$settings[config_sync_directory]` | `DRUPAL_CONFIG_SYNC_DIRECTORY` | `../conf/cmi` |
| `$settings[file_public_path]` | `DRUPAL_FILE_PUBLIC_PATH` | `sites/default/files` |
| `$settings[file_private_path]` | `DRUPAL_FILE_PRIVATE_PATH` | `FALSE` |
| `$settings[file_temp_path]` | `DRUPAL_FILE_TEMP_PATH` | `/tmp` |
| `$settings[hash_salt]` | `DRUPAL_HASH_SALT` | `0000000000000000` |

Each system's `getEnvs()` maps system-native env vars to `DRUPAL_*` names.

> **Upsun exception:** DB credentials are not individual env vars. They come from `PLATFORM_RELATIONSHIPS` (base64-encoded JSON of all service connections). `Upsun::getDatabaseCredentials()` decodes it and picks the first `mysql`/`pgsql` entry, regardless of relationship name.

## Running Tests

Tests run inside Docker using `druidfi/drupal:php-8.2`:

```bash
make test              # All environments
make test-lagoon       # Single environment
make test-ddev
make test-lando
make test-pantheon
make test-tugboat
make test-upsun
make test-wodby
make test-unknown
```

Each test variant runs 3 times with `APP_ENV=dev`, `APP_ENV=test`, `APP_ENV=prod`.

The `vendor/` directory must exist first (`composer install`).

## Adding a New Environment

1. Create `src/System/NewEnv.php` extending `AbstractSystem`
   - Set `$env_name` (the ENV var that identifies this system)
   - Set `$env_type_map` (map system env type values to `dev`/`test`/`prod`)
   - Override `getEnvs()` to map system DB/route vars to `DRUPAL_*`
   - Override `setConfiguration()` for system-specific `$config`/`$settings`
2. Add detection key → class to `Reader::MAP`
3. Create `tests/NewEnv/NewEnvTest.php` extending `BaseCase`, override expected values
4. Create `tests/NewEnv.xml` (PHPUnit config setting the detection ENV var and DB vars)
5. Add `test-newenv` script to `composer.json` scripts
6. Add `test-newenv` target to `Makefile`

## Debug / Inspection

To print all detected configuration in a browser:
```php
Druidfi\Omen\Reader::show(get_defined_vars());
```

Or at runtime via URL (requires `OMEN_TOKEN` env var set):
```
https://example.com/?_show_omens=<token>
```

## Version-Specific Behavior

- Drupal >= 10.3.0: `$settings['state_cache'] = TRUE` is set automatically
