<?php

/**
 * The `searchwp\source\attribute\label` filter.
 *
 * @see uamswp-searchwp-customizations.php
 */

declare(strict_types=1);

/**
 * Run the registered `searchwp\source\attribute\label` callback.
 *
 * @param  array<string, string>  $args
 */
function uamswp_source_label(string $label, array $args): string
{
    $result = uamswp_registered_hook('searchwp\source\attribute\label')['callback']($label, $args);

    if (! is_string($result)) {
        throw new UnexpectedValueException('The label callback must return a string.');
    }

    return $result;
}

it('renames the knowledge base source', function (): void {
    expect(uamswp_source_label('KB Articles', ['source' => 'post.kb']))->toBe('Knowledge Articles');
});

it('keeps the label for every other source', function (string $source): void {
    expect(uamswp_source_label('Some label', ['source' => $source]))->toBe('Some label');
})->with([
    'posts' => ['post.post'],
    'pages' => ['post.page'],
    'near miss' => ['post.kbx'],
    'empty' => [''],
]);
