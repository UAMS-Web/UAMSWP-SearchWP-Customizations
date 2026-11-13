<?php

/**
 * The `searchwp\query\before` filter: keep only the engine source named by `?type=`.
 *
 * @see uamswp-searchwp-customizations.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Functions\when;

function uamswp_source(string $name): object
{
    return new class($name)
    {
        public function __construct(private readonly string $name) {}

        public function get_name(): string
        {
            return $this->name;
        }
    };
}

/**
 * A query whose engine holds the given sources and records the ones removed.
 *
 * @param  list<string>  $sourceNames
 * @param  list<string>  $removed  filled by reference with each removed source name
 */
function uamswp_query(array $sourceNames, array &$removed): object
{
    $engine = new class(array_map(uamswp_source(...), $sourceNames), $removed)
    {
        /** @param list<object> $sources */
        public function __construct(private readonly array $sources, private array &$removed) {}

        /** @return list<object> */
        public function get_sources(): array
        {
            return $this->sources;
        }

        public function remove_source(string $name): void
        {
            $this->removed[] = $name;
        }
    };

    return new class($engine)
    {
        public function __construct(private readonly object $engine) {}

        public function get_engine(): object
        {
            return $this->engine;
        }
    };
}

function uamswp_run_query_before(object $query): mixed
{
    return uamswp_registered_hook('searchwp\query\before')['callback']($query);
}

afterEach(function (): void {
    unset($_GET['type']);
});

it('leaves the engine alone when there is no type', function (): void {
    $removed = [];
    Functions\expect('sanitize_text_field')->never();

    expect(uamswp_run_query_before(uamswp_query(['post.post', 'post.page'], $removed)))->toBeNull()
        ->and($removed)->toBe([]);
});

it('removes every source except the one named by the type', function (): void {
    $_GET['type'] = 'page';
    $removed = [];
    Functions\expect('sanitize_text_field')->once()->with('page')->andReturn('page');

    uamswp_run_query_before(uamswp_query(['post.post', 'post.page', 'post.kb'], $removed));

    expect($removed)->toBe(['source-for-post', 'source-for-kb']);
});

it('removes nothing when the only source is the one named', function (): void {
    $_GET['type'] = 'kb';
    $removed = [];
    when('sanitize_text_field')->returnArg();

    uamswp_run_query_before(uamswp_query(['post.kb'], $removed));

    expect($removed)->toBe([]);
});

it('compares the sanitized type, not the raw one', function (): void {
    $_GET['type'] = '<b>page</b>';
    $removed = [];
    when('sanitize_text_field')->justReturn('page');

    uamswp_run_query_before(uamswp_query(['post.page', 'post.post'], $removed));

    expect($removed)->toBe(['source-for-post']);
});
