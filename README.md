# UAMSWP SearchWP Customizations

Customizations for SearchWP on the UAMS Health WordPress multisite.

The settings, search behavior and troubleshooting are documented in the [wiki](https://github.com/UAMS-Web/UAMSWP-SearchWP-Customizations/wiki).

## Requirements

- WordPress 6.8 or later, as a multisite network.
- PHP 8.2 or later.
- SearchWP, with its multisite search.
- Advanced Custom Fields Pro, for the settings page on the main site. Without it, searches use the main site alone and 20 results per page.

The settings page appears on the main site only. See [Settings](https://github.com/UAMS-Web/UAMSWP-SearchWP-Customizations/wiki/Settings) in the wiki.

## Tests

The suite uses Pest 5 with Brain Monkey, so it runs with no database and no WordPress checkout. It needs PHP 8.4.1 or later and Composer.

```bash
composer install
composer test
```

`composer test` runs `vendor/bin/pest`. Tests live in `tests/Unit/` and are named `*Test.php`. The Composer dependencies are for development only; none ship with the plugin.

CI runs every check on each pull request. To run the main ones before opening it:

```bash
composer test
composer test:pint
composer test:refactor
composer stan
```

## Credits

Developed and maintained by UAMS Web Services.

Developers:

- Josh Daugherty
- Todd McKee

## License

GPL-2.0-or-later. See [`LICENSE`](LICENSE).

Copyright 2022 UAMS Web Services
