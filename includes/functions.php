<?php

declare(strict_types=1);

use SearchWP\Engine;
use SearchWP\Query;
use SearchWP\Utils;

// Generate list of subsites for field group
/**
 * @return non-falsy-string[]
 */
function get_subsites_for_search(): array
{
    $subsites = get_sites();
    $array = [];
    foreach ($subsites as $subsite) {
        $subsite_id = $subsite->blog_id;
        $details = get_blog_details($subsite_id);
        // get_blog_details() returns false when the site's details cannot be read, for example a site deleted since get_sites() ran.
        if ($details === false) {
            continue;
        }

        $array[$subsite_id] = $details->blogname.' (ID: '.$subsite_id.')';
    }

    return $array;
}

// Generate list of post types for filter
function uamswp_search_post_type_links(): string
{
    $engine = new Engine('default');
    $sources = array_keys($engine->get_sources());
    $type = '';

    // A non-string value (`?type[]=x`) reads as no type, which is what sanitize_text_field() returns for an array.
    if (isset($_GET['type']) && is_string($_GET['type'])) {
        $type = sanitize_text_field($_GET['type']);
    }

    $links = '<ul class="nav">';
    if (empty($type)) {
        $links .= '<li class="nav-item active pr-2 font-weight-bold">All</li>';
    } else {
        $links .= '<li class="nav-item pr-2"><a class="" href="'.esc_url(remove_query_arg('type', get_pagenum_link(1, false))).'">All</a></li> ';
    }

    foreach ($sources as $source) {
        // Equivalent mutant: only strlen($flag) is used, and swapping the two operands of the concatenation does not change its length.
        // @pest-mutate-ignore:ConcatSwitchSides
        $flag = 'post'.SEARCHWP_SEPARATOR;
        $post_name = substr($source, strlen($flag));
        $source_type = $engine->get_source($source);
        $source_label = $source_type->get_label();
        // if ('KB Articles' === $source_label){
        // 	$source_label = 'Knowlege Articles';
        // }
        // $label = $source_type['labels']['singular'];
        $post_type_name = $source_label;
        if ($post_type_name === 'Posts') {
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

/**
 * The `searchwp\query\args` filter: search the sites listed in the settings, or site 1 alone.
 *
 * Tells SearchWP to search the entire Multisite network when searching on the main site.
 *
 * @link https://searchwp.com/documentation/multisite/
 *
 * The filter is registered with two arguments, and the query it passes second is not read.
 *
 * @param  array<string, mixed>  $args
 * @param  mixed  $query
 * @return array<string, mixed>
 */
function uamswp_searchwp_query_args($args, $query)
{
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
}

/**
 * The `searchwp\query\before` filter: remove every engine source but the one named by `?type=`.
 *
 * @param  Query  $query
 */
function uamswp_searchwp_query_before($query): void
{
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
}

/**
 * The `searchwp\source\attribute\label` filter: rename the knowledge base source.
 *
 * @param  string  $label
 * @param  array<string, mixed>  $args
 * @return string
 */
function uamswp_searchwp_source_label($label, $args = [])
{
    if ($args['source'] === 'post.kb') {
        return 'Knowledge Articles';
    }

    return $label;
}
