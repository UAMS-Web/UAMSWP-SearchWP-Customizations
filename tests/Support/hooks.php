<?php

/**
 * Captures the hook registrations the plugin file makes when it loads.
 *
 * The plugin registers its callbacks as closures. Brain Monkey keeps a printable
 * form of a registered callback, not the closure, so add_filter() and
 * add_action() are redefined while the file loads and each registration is
 * returned for the tests to call.
 */

declare(strict_types=1);

use function Patchwork\redefine;
use function Patchwork\relay;
use function Patchwork\restore;

/**
 * Load the main plugin file and return the hook registrations it makes, by hook name.
 *
 * Must run inside a test: it needs Brain Monkey's add_filter() and add_action() to exist.
 * Each call loads the file again, which registers the hooks again.
 *
 * @return array<string, list<array{callback: callable, priority: int, accepted_args: int}>>
 */
function uamswp_registered_hooks(): array
{
    $registered = [];

    $capture = static function (string $hook, callable $callback, int $priority = 10, int $accepted_args = 1) use (&$registered): bool {
        $registered[$hook][] = [
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];

        return true;
    };

    $redefinitions = [
        redefine('add_filter', $capture),
        redefine('add_action', $capture),
    ];

    require dirname(__DIR__, 2).'/uamswp-searchwp-customizations.php';

    array_map(restore(...), $redefinitions);

    return $registered;
}

/**
 * @return array{callback: callable, priority: int, accepted_args: int}
 */
function uamswp_registered_hook(string $hook): array
{
    $registrations = uamswp_registered_hooks()[$hook] ?? [];

    expect($registrations)->toHaveCount(1);

    return $registrations[0];
}

/**
 * Make class_exists() and function_exists() answer for the named symbols,
 * and ask PHP for any other.
 *
 * @param  array<string, bool>  $classes
 * @param  array<string, bool>  $functions
 */
function uamswp_fake_symbols(array $classes = [], array $functions = []): void
{
    redefine('class_exists', function (...$arguments) use ($classes) {
        $name = $arguments[0];

        return array_key_exists($name, $classes) ? $classes[$name] : relay($arguments);
    });

    redefine('function_exists', function (...$arguments) use ($functions) {
        $name = $arguments[0];

        return array_key_exists($name, $functions) ? $functions[$name] : relay($arguments);
    });
}
