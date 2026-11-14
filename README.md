# UAMSWP SearchWP Customizations

Customizations for SearchWP on the UAMS Health WordPress multisite.

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
