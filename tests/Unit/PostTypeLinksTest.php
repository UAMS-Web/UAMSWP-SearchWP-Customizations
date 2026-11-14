<?php

/**
 * uamswp_search_post_type_links(): the "All / News / ..." filter links above search results.
 *
 * @see uamswp-searchwp-customizations.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;
use SearchWP\Engine;
use SearchWP\Source;

use function Brain\Monkey\Functions\stubEscapeFunctions;
use function Brain\Monkey\Functions\when;

/**
 * @param  array<string, string>  $labelsByName  source name => label
 */
function uamswp_set_sources(array $labelsByName): void
{
    Engine::$sources = [];

    foreach ($labelsByName as $name => $label) {
        Engine::$sources[$name] = new Source($label);
    }
}

beforeEach(function (): void {
    Engine::$sources = [];
    Engine::$constructedWith = null;
    stubEscapeFunctions();
    when('get_pagenum_link')->alias(fn (int $page, bool $escape): string => "https://example.org/search/page/{$page}/?escape=".var_export($escape, true));
    when('remove_query_arg')->alias(fn (string $key, string $url): string => "{$url}&removed={$key}");
    when('add_query_arg')->alias(fn (string $key, string $value, string $url): string => "{$url}&{$key}={$value}");
    when('sanitize_text_field')->returnArg();
});

afterEach(function (): void {
    unset($_GET['type']);
});

it('builds the list from the default engine', function (): void {
    uamswp_search_post_type_links();

    expect(Engine::$constructedWith)->toBe('default');
});

it('returns an empty list holding only the active All item when the engine has no sources', function (): void {
    expect(uamswp_search_post_type_links())->toBe('<ul class="nav"><li class="nav-item active pr-2 font-weight-bold">All</li></ul>');
});

it('marks All active and links every source when no type is chosen', function (): void {
    uamswp_set_sources(['post.post' => 'Posts', 'post.page' => 'Pages']);

    expect(uamswp_search_post_type_links())->toBe(
        '<ul class="nav">'
        .'<li class="nav-item active pr-2 font-weight-bold">All</li>'
        .'<li class="nav-item pr-2"><a class="" href="https://example.org/search/page/1/?escape=false&type=post">News</a></li>'
        .'<script>{"label":"Posts"}</script>'
        .'<li class="nav-item pr-2"><a class="" href="https://example.org/search/page/1/?escape=false&type=page">Pages</a></li>'
        .'<script>{"label":"Pages"}</script>'
        .'</ul>'
    );
});

it('links All back and marks the chosen source active', function (): void {
    $_GET['type'] = 'page';
    uamswp_set_sources(['post.post' => 'Posts', 'post.page' => 'Pages']);

    expect(uamswp_search_post_type_links())->toBe(
        '<ul class="nav">'
        .'<li class="nav-item pr-2"><a class="" href="https://example.org/search/page/1/?escape=false&removed=type">All</a></li> '
        .'<li class="nav-item pr-2"><a class="" href="https://example.org/search/page/1/?escape=false&type=post">News</a></li>'
        .'<script>{"label":"Posts"}</script>'
        .'<li class="nav-item active pr-2 font-weight-bold">Pages</li>'
        .'<script>{"label":"Pages"}</script>'
        .'</ul>'
    );
});

it('shows the Posts source as News, whether it is linked or active', function (): void {
    $_GET['type'] = 'post';
    uamswp_set_sources(['post.post' => 'Posts']);

    $html = uamswp_search_post_type_links();

    expect($html)->toContain('<li class="nav-item active pr-2 font-weight-bold">News</li>');
    expect(str_contains($html, '>Posts<'))->toBeFalse();
});

it('keeps every other label as SearchWP gave it', function (): void {
    uamswp_set_sources(['post.kb' => 'Posts and more']);

    expect(uamswp_search_post_type_links())->toContain('>Posts and more</a>');
});

it('sanitizes the type it reads', function (): void {
    $_GET['type'] = '<script>page';
    $seen = [];
    when('sanitize_text_field')->alias(function (string $value) use (&$seen): string {
        $seen[] = $value;

        return 'page';
    });
    uamswp_set_sources(['post.page' => 'Pages']);

    expect(uamswp_search_post_type_links())->toContain('<li class="nav-item active pr-2 font-weight-bold">Pages</li>')
        ->and($seen)->toBe(['<script>page']);
});

it('treats a type given as an array as no type, without sanitizing it', function (): void {
    $_GET['type'] = ['page'];
    Functions\expect('sanitize_text_field')->never();
    uamswp_set_sources(['post.page' => 'Pages']);

    expect(uamswp_search_post_type_links())->toStartWith('<ul class="nav"><li class="nav-item active pr-2 font-weight-bold">All</li>');
});

it('treats a type that is empty after sanitizing as no type', function (string $type): void {
    $_GET['type'] = $type;
    when('sanitize_text_field')->justReturn($type === '0' ? '0' : '');
    uamswp_set_sources(['post.page' => 'Pages']);

    expect(uamswp_search_post_type_links())->toStartWith('<ul class="nav"><li class="nav-item active pr-2 font-weight-bold">All</li>');
})->with([
    'blank' => [''],
    'zero' => ['0'],
]);

it('compares the chosen type with a source loosely, so numeric strings that are equal count as the same', function (): void {
    $_GET['type'] = '1e1';
    uamswp_set_sources(['post.10' => 'Tens']);

    expect(uamswp_search_post_type_links())->toContain('<li class="nav-item active pr-2 font-weight-bold">Tens</li>');
});
