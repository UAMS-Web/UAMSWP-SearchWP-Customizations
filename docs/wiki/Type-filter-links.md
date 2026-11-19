# Type filter links

Written against v1.0.1.

`uamswp_search_post_type_links()` returns an HTML list with one item per source in SearchWP's `default` engine, plus **All**. The current item is plain text; every other item is a link. A theme's search template prints it above the results, so a reader can narrow a search to one type. The links set the `type` query argument described in [Search behavior](Search-behavior.md#narrowing-results-to-one-type).

The plugin defines the function but never prints it; the theme decides where it goes.

## Usage

```php
<?php echo uamswp_search_post_type_links(); ?>
```

The URLs and the labels in the return value are escaped.

## Output

| Part | Markup |
| --- | --- |
| The list | `<ul class="nav">` |
| The current type, or **All** when there is no `type` | `<li class="nav-item active pr-2 font-weight-bold">` with the label and no link |
| Every other type, and **All** when a type is set | `<li class="nav-item pr-2">` with a link to the first page of the current search, `type` added or removed |

The theme's styles decide how the list looks.

## Labels

Each link uses the label SearchWP gives the source (`get_label()`), except that `Posts` is shown as `News`. Each label is escaped with `esc_html()`.

## Example

On a search with sources for posts and pages and no `type` set, the list holds **All** (current, no link), then a `News` link and a `Pages` link. Each link points at the first page of the current search (`get_pagenum_link( 1 )`) with `type=post` or `type=page` added, so its exact form follows the site's permalink structure.

**Note:** v1.0.1 added a `<script>` element holding `json_encode()` of the SearchWP source object after each type's list item, and printed the labels without escaping them. #74 removes the element and escapes each label with `esc_html()`, so a label containing `<` is printed as `&lt;`.
