<?php

/**
 * The `searchwp\query\per_page` filter.
 *
 * @see uamswp-searchwp-customizations.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Functions\when;

function uamswp_per_page(mixed $perPage = 10): mixed
{
    return uamswp_registered_hook('searchwp\query\per_page')['callback']($perPage, []);
}

it('returns the number saved in the settings', function (): void {
    Functions\expect('get_field')
        ->once()
        ->with('uams_searchwp_results_per_page', 'option')
        ->andReturn(12);

    expect(uamswp_per_page())->toBe(12);
});

it('returns 20 when nothing is saved', function (mixed $saved): void {
    when('get_field')->justReturn($saved);

    expect(uamswp_per_page(7))->toBe(20);
})->with([
    'nothing saved' => [false],
    'null' => [null],
    'zero' => [0],
    'empty string' => [''],
]);
