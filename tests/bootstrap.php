<?php

/**
 * Unit bootstrap.
 *
 * The Unit suite runs with no WordPress: WordPress functions are mocked per
 * test with Brain Monkey.
 */

declare(strict_types=1);

use function Patchwork\redefine;

require_once __DIR__.'/../vendor/autoload.php';

/*
 * Patchwork first: it can only redefine functions declared in files loaded
 * after it, so loading it before the plugin is what lets a test mock one of the
 * plugin's own functions, not only WordPress'.
 */
require_once __DIR__.'/../vendor/antecedent/patchwork/Patchwork.php';

/*
 * Define ABSPATH, pointing at a directory that is not a WordPress install, so
 * code that requires a core file through it fails loudly here.
 */
if (! defined('ABSPATH')) {
    define('ABSPATH', sys_get_temp_dir().'/uamswp-searchwp-customizations-tests-no-wordpress/');
}

/*
 * Load the files that DEFINE the plugin's functions, never the main plugin file: it
 * registers hooks at file scope, and Brain Monkey's add_action() and add_filter()
 * exist only inside a test. Tests that need the registrations load the main file
 * themselves (tests/Support/hooks.php).
 */
require_once __DIR__.'/Support/searchwp.php';
require_once __DIR__.'/Support/hooks.php';
require_once __DIR__.'/../includes/functions.php';
require_once __DIR__.'/../includes/hooks.php';

// cspell:ignore ABSPATH uamswp
