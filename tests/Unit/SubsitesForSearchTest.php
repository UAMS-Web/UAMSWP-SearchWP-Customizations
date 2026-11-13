<?php

/**
 * get_subsites_for_search(), with WordPress mocked.
 *
 * @see uamswp-searchwp-customizations.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Functions\when;

it('labels each site with its name and ID, keyed by blog ID', function (): void {
    Functions\expect('get_sites')
        ->once()
        ->andReturn([
            (object) ['blog_id' => 1],
            (object) ['blog_id' => 7],
        ]);

    Functions\expect('get_blog_details')
        ->twice()
        ->andReturnUsing(fn (int $id): object => (object) [
            'blogname' => $id === 1 ? 'UAMS Health' : 'Cardiology',
        ]);

    expect(get_subsites_for_search())->toBe([
        1 => 'UAMS Health (ID: 1)',
        7 => 'Cardiology (ID: 7)',
    ]);
});

it('returns an empty list when there are no sites', function (): void {
    when('get_sites')->justReturn([]);

    expect(get_subsites_for_search())->toBe([]);
});

// cspell:ignore Cardiology
