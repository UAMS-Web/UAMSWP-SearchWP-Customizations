<?php

declare(strict_types=1);

/**
 * Register the plugin's SearchWP and ACF hooks.
 */
function uamswp_searchwp_customizations_register_hooks(): void
{
    // @link https://searchwp.com/documentation/multisite/
    // Tell SearchWP to search the entire Multisite network when searching on the main site.
    add_filter('searchwp\query\args', uamswp_searchwp_query_args(...), 10, 2);

    // Function to add ACF settings and fields
    add_action('acf/init', function (): void {
        // Add Settings Page
        if (function_exists('acf_add_options_page')) {
            if (get_current_blog_id() !== 1) {
                return;
            }

            acf_add_options_sub_page([
                'page_title' => 'Multisite Search Settings',
                'menu_slug' => 'uamswp-searchwp-sites',
                'menu_title' => 'UAMS Search Settings',
                'capability' => 'manage_options',
                'position' => '100',
                'parent_slug' => 'uamswp-settings',
                'redirect' => false,
            ]);
        }

        // Add Search field group
        if (function_exists('acf_add_local_field_group')) {
            if (get_current_blog_id() !== 1) {
                return;
            }

            acf_add_local_field_group([
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
                        'choices' => get_subsites_for_search(),
                        'allow_custom' => 0,
                        'default_value' => [
                            1 => get_bloginfo('name'),
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
            ]);
        }
    });

    // Set results per page for SearchWP results.
    add_filter('searchwp\query\per_page', function ($per_page, $args) {
        $results_per_page = get_field('uams_searchwp_results_per_page', 'option');

        // Default value
        if (empty($results_per_page)) {
            $results_per_page = 20;
        }

        return $results_per_page;
    }, 30, 2);

    // Conditionally remove a SearchWP Engine Source.
    add_filter('searchwp\query\before', uamswp_searchwp_query_before(...), 20, 2);

    add_filter('searchwp\source\attribute\label', uamswp_searchwp_source_label(...), 20, 2);
}
