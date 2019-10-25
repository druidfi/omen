# druidfi/omen [Work in Progress]

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

## Known environments

- [Amazee.io Lagoon](https:)
- [Amazee.io Legacy](https:)
- [Wodby](https://Wodby)

## TODO

Add support for:

- Lando
- Drupal VM
- Other dev tools and environments

## Where the name "Omen" comes from?

Druids interpreted the waves of the ocean or read clouds for mundane or important omens. So `reading clouds` is
basically what `druidfi/omen` is doing.

## Other information

This project is found from the Packagist: https://packagist.org/packages/druidfi/omen
