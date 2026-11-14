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
    final class Source
    {
        public function __construct(public string $label = '', private readonly string $name = '') {}

        public function get_label(): string
        {
            return $this->label;
        }

        public function get_name(): string
        {
            return $this->name;
        }
    }

    /**
     * Named only so the plugin's docblocks can type the query the `searchwp\query\before`
     * filter receives; the tests pass their own objects.
     */
    abstract class Query
    {
        abstract public function get_engine(): Engine;
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

        public function remove_source(string $name): void
        {
            unset(self::$sources[$name]);
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
