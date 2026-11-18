# Troubleshooting

Written against v1.0.1.

- [A search on the main site returns results from the main site only](#a-search-on-the-main-site-returns-results-from-the-main-site-only)
- [The UAMS Search Settings page is missing from the admin menu](#the-uams-search-settings-page-is-missing-from-the-admin-menu)
- [A search with a type argument returns no results](#a-search-with-a-type-argument-returns-no-results)
- [A search on another site ignores the selected sites](#a-search-on-another-site-ignores-the-selected-sites)

## A search on the main site returns results from the main site only

**Cause:** no site is selected under **Sites to include**, so the plugin falls back to the main site alone.

**Fix:** open **UAMS Search Settings** on the main site, check the sites to search and save. See [Settings](Settings.md#fields).

## The UAMS Search Settings page is missing from the admin menu

**Cause:** one of these:

- The admin screen belongs to a site other than the main site (ID 1). The page is registered on the main site only.
- ACF Pro is not active, so `acf_add_options_page()` does not exist.
- No menu with the slug `uamswp-settings` is registered, so the page may have no parent to appear under.
- The user lacks the `manage_options` capability.

**Fix:** open the main site's admin as an administrator, with ACF Pro active and the plugin or theme that registers `uamswp-settings` active.

## A search with a type argument returns no results

**Cause:** the `type` value is empty, does not match the post type name of any source in the engine the search uses (for example `?type=pages` where the post type is `page`), or was passed as an array. Every post-type source is then removed. See [Search behavior](Search-behavior.md#narrowing-results-to-one-type).

**Fix:** use the post type name, as the links from [Type filter links](Type-filter-links.md) do, and check that the post type is a source of the engine the search uses, under SearchWP's engine settings.

## A search on another site ignores the selected sites

**Cause:** by design. The selected sites apply to searches on the main site only; on any other site the plugin leaves SearchWP's query arguments unchanged.

**Fix:** none needed. To search across the network, search from the main site.
