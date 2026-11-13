<?php

/**
 * The `acf/init` action: the settings page and the field group.
 *
 * @see uamswp-searchwp-customizations.php
 */

declare(strict_types=1);

use Brain\Monkey\Functions;

use function Brain\Monkey\Functions\when;

function uamswp_run_acf_init(): void
{
    uamswp_registered_hook('acf/init')['callback']();
}

/**
 * @return array<string, mixed>
 */
function uamswp_expected_options_page(): array
{
    return [
        'page_title' => 'Multisite Search Settings',
        'menu_slug' => 'uamswp-searchwp-sites',
        'menu_title' => 'UAMS Search Settings',
        'capability' => 'manage_options',
        'position' => '100',
        'parent_slug' => 'uamswp-settings',
        'redirect' => false,
    ];
}

/**
 * @return array<string, mixed>
 */
function uamswp_expected_field_group(): array
{
    return [
        'key' => 'group_uamswp_subsite_search',
        'title' => 'UAMS Subsite Search',
        'fields' => [
            [
                'key' => 'field_uams_searchwp_sites',
                'label' => 'Sites to include',
                'name' => 'uams_searchwp_sites',
                'type' => 'checkbox',
                'instructions' => 'Sites to include in the search. Leave ',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'choices' => [1 => 'UAMS Health (ID: 1)', 5 => 'Cardiology (ID: 5)'],
                'allow_custom' => 0,
                'default_value' => [
                    1 => 'UAMS Health',
                ],
                'layout' => 'vertical',
                'toggle' => 0,
                'return_format' => 'value',
                'save_custom' => 0,
            ],
            [
                'key' => 'field_uams_searchwp_results_per_page',
                'label' => 'Results per page',
                'name' => 'uams_searchwp_results_per_page',
                'type' => 'number',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => 20,
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'min' => 5,
                'max' => 30,
                'step' => 1,
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'uamswp-searchwp-sites',
                ],
            ],
        ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'left',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ];
}

function uamswp_stub_two_sites(): void
{
    when('get_sites')->justReturn([(object) ['blog_id' => 1], (object) ['blog_id' => 5]]);
    when('get_blog_details')->alias(fn (int $id): object => (object) ['blogname' => $id === 1 ? 'UAMS Health' : 'Cardiology']);
    when('get_bloginfo')->justReturn('UAMS Health');
}

it('does nothing when ACF has neither function', function (): void {
    when('get_current_blog_id')->justReturn(1);
    Functions\expect('acf_add_options_sub_page')->never();
    Functions\expect('acf_add_local_field_group')->never();
    uamswp_fake_symbols(functions: ['acf_add_options_page' => false, 'acf_add_local_field_group' => false]);

    uamswp_run_acf_init();
});

it('adds the settings page and then the field group on site 1', function (): void {
    when('get_current_blog_id')->justReturn(1);
    uamswp_stub_two_sites();
    $calls = [];
    Functions\expect('acf_add_options_sub_page')->once()->andReturnUsing(function (array $page) use (&$calls): void {
        $calls[] = ['page', $page];
    });
    Functions\expect('acf_add_local_field_group')->once()->andReturnUsing(function (array $group) use (&$calls): void {
        $calls[] = ['group', $group];
    });
    uamswp_fake_symbols(functions: ['acf_add_options_page' => true, 'acf_add_local_field_group' => true]);

    uamswp_run_acf_init();

    expect($calls)->toBe([
        ['page', uamswp_expected_options_page()],
        ['group', uamswp_expected_field_group()],
    ]);
});

it('adds only the settings page when the field group function is missing', function (): void {
    when('get_current_blog_id')->justReturn(1);
    Functions\expect('acf_add_options_sub_page')->once()->with(uamswp_expected_options_page());
    Functions\expect('acf_add_local_field_group')->never();
    uamswp_fake_symbols(functions: ['acf_add_options_page' => true, 'acf_add_local_field_group' => false]);

    uamswp_run_acf_init();
});

it('adds only the field group when the settings page function is missing', function (): void {
    when('get_current_blog_id')->justReturn(1);
    uamswp_stub_two_sites();
    Functions\expect('acf_add_options_sub_page')->never();
    Functions\expect('acf_add_local_field_group')->once()->with(uamswp_expected_field_group());
    uamswp_fake_symbols(functions: ['acf_add_options_page' => false, 'acf_add_local_field_group' => true]);

    uamswp_run_acf_init();
});

it('adds nothing on any site but the first', function (bool $hasOptionsPage, bool $hasFieldGroup): void {
    when('get_current_blog_id')->justReturn(3);
    Functions\expect('get_sites')->never();
    Functions\expect('acf_add_options_sub_page')->never();
    Functions\expect('acf_add_local_field_group')->never();
    uamswp_fake_symbols(functions: ['acf_add_options_page' => $hasOptionsPage, 'acf_add_local_field_group' => $hasFieldGroup]);

    uamswp_run_acf_init();
})->with([
    'both functions' => [true, true],
    'settings page only' => [true, false],
    'field group only' => [false, true],
]);
