<?php

/**
 * Unit bootstrap.
 *
 * The Unit suite runs with no WordPress: WordPress functions are mocked per
 * test with Brain Monkey.
 */

declare(strict_types=1);

use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

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
 * The plugin defines its functions and registers its hooks in one file, so the
 * file is loaded inside a single Brain Monkey session: add_action() and
 * add_filter() exist during it, and the registrations are discarded when it
 * ends. The functions the file defines outlive the session. Nothing in the
 * file calls another WordPress function while loading, so no stubs are needed.
 *
 * Splitting the definitions from the registrations is the first change this
 * harness should pin before making (UAMS-Web/uams-claude-skills tests skill,
 * "Pinning legacy behavior before a change").
 */
setUp();
require_once __DIR__.'/../uamswp-searchwp-customizations.php';
tearDown();

// cspell:ignore ABSPATH uamswp
