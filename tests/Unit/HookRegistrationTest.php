<?php

/**
 * Which hooks the plugin file registers, at which priority and with how many arguments.
 *
 * @see uamswp-searchwp-customizations.php
 */

declare(strict_types=1);

use Brain\Monkey\Actions;

use function Brain\Monkey\Filters\expectAdded;

it('registers each callback on its hook with the priority and argument count SearchWP is given', function (): void {
    expectAdded('searchwp\query\args')->once()->with(Mockery::type(Closure::class), 10, 2);
    Actions\expectAdded('acf/init')->once()->with(Mockery::type(Closure::class), 10, 1);
    expectAdded('searchwp\query\per_page')->once()->with(Mockery::type(Closure::class), 30, 2);
    expectAdded('searchwp\query\before')->once()->with(Mockery::type(Closure::class), 20, 2);
    expectAdded('searchwp\source\attribute\label')->once()->with(Mockery::type(Closure::class), 20, 2);

    require dirname(__DIR__, 2).'/uamswp-searchwp-customizations.php';
});

it('registers each captured callback as a closure with the same priority and argument count', function (string $hook, int $priority, int $acceptedArgs): void {
    $registration = uamswp_registered_hook($hook);

    expect($registration['priority'])->toBe($priority)
        ->and($registration['accepted_args'])->toBe($acceptedArgs)
        ->and($registration['callback'])->toBeInstanceOf(Closure::class);
})->with([
    'query args' => ['searchwp\query\args', 10, 2],
    'settings and fields' => ['acf/init', 10, 1],
    'results per page' => ['searchwp\query\per_page', 30, 2],
    'remove sources' => ['searchwp\query\before', 20, 2],
    'source label' => ['searchwp\source\attribute\label', 20, 2],
]);

it('registers exactly those five hooks', function (): void {
    expect(array_keys(uamswp_registered_hooks()))->toBe([
        'searchwp\query\args',
        'acf/init',
        'searchwp\query\per_page',
        'searchwp\query\before',
        'searchwp\source\attribute\label',
    ]);
});
