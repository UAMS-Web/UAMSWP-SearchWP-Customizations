<?php

declare(strict_types=1);

use SearchWP\Utils;

/**
 * Register the plugin's SearchWP and ACF hooks.
 */
function uamswp_searchwp_customizations_register_hooks(): void
{
    // @link https://searchwp.com/documentation/multisite/
    // Tell SearchWP to search the entire Multisite network when searching on the main site.
    add_filter('searchwp\query\args', function ($args, $query) {
        // If this is not site 1, bail out.
        if (get_current_blog_id() !== 1) {
            return $args;
        }

        $search_sites = [];
        if (class_exists('ACF')) {
            $search_sites = get_field('uams_searchwp_sites', 'option');
        }

        if (empty($search_sites)) {
            $search_sites = [1];
        }

        // Search sites listed in the settings.
        $args['site'] = $search_sites;

        // Retain site info in results.
        $args['fields'] = 'default';

        return $args;
    }, 10, 2);

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
    add_filter('searchwp\query\before', function ($query): void {
        // Applies only if `type` GET variable exists.
        if (! isset($_GET['type'])) {
            return;
        }

        // A non-string value (`?type[]=x`) reads as an empty type, which is what sanitize_text_field() returns for an array.
        $type = is_string($_GET['type']) ? sanitize_text_field($_GET['type']) : '';

        foreach ($query->get_engine()->get_sources() as $source) {
            // Equivalent mutant: only strlen($flag) is used, and swapping the two operands of the concatenation does not change its length.
            // @pest-mutate-ignore:ConcatSwitchSides
            $flag = 'post'.SEARCHWP_SEPARATOR;
            $source_name = substr($source->get_name(), strlen($flag));
            if ($type !== $source_name) {
                // Remove Posts from Engine.
                $source_name = Utils::get_post_type_source_name($source_name);
                $query->get_engine()->remove_source($source_name);
            }
        }

    }, 20, 2);

    add_filter('searchwp\source\attribute\label', function ($label, $args = []) {
        if ($args['source'] === 'post.kb') {
            return 'Knowledge Articles';
        }

        return $label;
    }, 20, 2);
}
