<?php

/**
 * Stand-ins for the SearchWP symbols the plugin file uses.
 *
 * SearchWP is not installed in the Unit suite, so the two classes the plugin
 * names and the constant it reads exist here with only what the plugin calls.
 * Each test sets the static fixtures it needs; Pest.php resets them.
 */

declare(strict_types=1);

namespace SearchWP {
    abstract class Source
    {
        abstract public function get_label(): string;
    }

    final class Engine
    {
        /** @var array<string, Source> */
        public static array $sources = [];

        public static ?string $constructedWith = null;

        public function __construct(string $name)
        {
            self::$constructedWith = $name;
        }

        /** @return array<string, Source> */
        public function get_sources(): array
        {
            return self::$sources;
        }

        public function get_source(string $name): Source
        {
            return self::$sources[$name];
        }
    }

    final class Utils
    {
        public static function get_post_type_source_name(string $post_type): string
        {
            return 'source-for-'.$post_type;
        }
    }
}

namespace {
    if (! defined('SEARCHWP_SEPARATOR')) {
        define('SEARCHWP_SEPARATOR', '.');
    }
}
