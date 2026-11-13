<?php

declare(strict_types=1);
use SearchWP\Engine;
use SearchWP\Utils;

/*
Plugin Name: UAMSWP SearchWP Customizations
Plugin URI: https://www.uams.edu
Description: Customizations for SearchWP
Version: 1.0.0
Requires PHP: 8.2
Author: UAMS Web Services
Author URI: https://communications.uams.edu/web/
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Copyright 2022 UAMS Web Services
*/
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

// Generate list of subsites for field group
/**
 * @return non-falsy-string[]
 */
function get_subsites_for_search(): array
{
    $subsites = get_sites();
    $array = [];
    foreach ($subsites as $subsite) {
        $subsite_id = get_object_vars($subsite)['blog_id'];
        $subsite_name = get_blog_details($subsite_id)->blogname;
        $array[$subsite_id] = $subsite_name.' (ID: '.$subsite_id.')';
    }

    return $array;
}

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

    $type = sanitize_text_field($_GET['type']);

    foreach ($query->get_engine()->get_sources() as $source) {
        $flag = 'post'.SEARCHWP_SEPARATOR;
        $source_name = substr($source->get_name(), strlen($flag));
        if ($type !== $source_name) {
            // Remove Posts from Engine.
            $source_name = Utils::get_post_type_source_name($source_name);
            $query->get_engine()->remove_source($source_name);
        }
    }

}, 20, 2);

// Generate list of post types for filter
function uamswp_search_post_type_links(): string
{
    $engine = new Engine('default');
    $sources = array_keys($engine->get_sources());
    $type = '';

    if (isset($_GET['type'])) {
        $type = sanitize_text_field($_GET['type']);
    }

    $links = '<ul class="nav">';
    if (empty($type)) {
        $links .= '<li class="nav-item active pr-2 font-weight-bold">All</li>';
    } else {
        $links .= '<li class="nav-item pr-2"><a class="" href="'.esc_url(remove_query_arg('type', get_pagenum_link(1, false))).'">All</a></li> ';
    }

    foreach ($sources as $source) {
        $flag = 'post'.SEARCHWP_SEPARATOR;
        $post_name = substr($source, strlen($flag));
        $post_type = get_post_type_object($post_name);
        // $post_type_name = $post_type->labels->name;
        $source_type = $engine->get_source($source);
        $source_label = $source_type->get_label();
        // if ('KB Articles' === $source_label){
        // 	$source_label = 'Knowlege Articles';
        // }
        // $label = $source_type['labels']['singular'];
        $post_type_name = $source_label;
        if ($post_type_name == 'Posts') {
            $post_type_name = 'News';
        }

        if ($type == $post_name) {
            $links .= '<li class="nav-item active pr-2 font-weight-bold">'.$post_type_name.'</li>';
        } else {
            // $link = get_pagenum_link();
            $links .= '<li class="nav-item pr-2"><a class="" href="'.esc_url(add_query_arg('type', $post_name, get_pagenum_link(1, false))).'">'.$post_type_name.'</a></li>';
        }

        $links .= '<script>'.json_encode($source_type).'</script>';
    }

    $links .= '</ul>';

    return $links;
}

add_filter('searchwp\source\attribute\label', function ($label, $args = []) {
    if ($args['source'] === 'post.kb') {
        return 'Knowledge Articles';
    }

    return $label;
}, 20, 2);
