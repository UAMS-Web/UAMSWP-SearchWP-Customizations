<?php

/**
 * The `searchwp\query\args` filter: search the network's sites from site 1.
 *
 * @see uamswp-searchwp-customizations.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Functions\when;

function uamswp_query_args(array $args): array
{
    return uamswp_registered_hook('searchwp\query\args')['callback']($args, new stdClass);
}

it('leaves the arguments alone on any site but the first', function (): void {
    when('get_current_blog_id')->justReturn(2);
    Functions\expect('get_field')->never();

    $args = ['site' => [9], 'fields' => 'ids', 'other' => 'kept'];

    expect(uamswp_query_args($args))->toBe($args);
});

it('searches site 1 alone when ACF is not loaded', function (): void {
    when('get_current_blog_id')->justReturn(1);
    Functions\expect('get_field')->never();
    uamswp_fake_symbols(classes: ['ACF' => false]);

    expect(uamswp_query_args(['s' => 'heart']))->toBe([
        's' => 'heart',
        'site' => [1],
        'fields' => 'default',
    ]);
});

it('searches the sites chosen in the settings', function (): void {
    when('get_current_blog_id')->justReturn(1);
    Functions\expect('get_field')
        ->once()
        ->with('uams_searchwp_sites', 'option')
        ->andReturn([1, 4, 7]);
    uamswp_fake_symbols(classes: ['ACF' => true]);

    expect(uamswp_query_args(['site' => [99], 'fields' => 'ids']))->toBe([
        'site' => [1, 4, 7],
        'fields' => 'default',
    ]);
});

it('falls back to site 1 when no sites are chosen', function (mixed $chosen): void {
    when('get_current_blog_id')->justReturn(1);
    when('get_field')->justReturn($chosen);
    uamswp_fake_symbols(classes: ['ACF' => true]);

    expect(uamswp_query_args([])['site'])->toBe([1]);
})->with([
    'empty list' => [[]],
    'nothing saved' => [false],
    'null' => [null],
]);
