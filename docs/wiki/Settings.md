# Settings

Written against v1.0.1.

The plugin adds one options page, on the network's main site (site ID 1) only. It is built with Advanced Custom Fields (ACF) options pages, so it appears only when ACF provides `acf_add_options_page()` (ACF Pro).

| Item | Value |
| --- | --- |
| Menu title | UAMS Search Settings |
| Page title | Multisite Search Settings |
| Menu slug | `uamswp-searchwp-sites` |
| Parent menu | `uamswp-settings` (a UAMS settings menu that another plugin or the theme registers) |
| Capability | `manage_options` |

**Note:** the page is registered under the parent slug `uamswp-settings`. If no plugin or theme registers that menu, the page may not appear.

## Fields

Both fields are stored as ACF options (`get_field( '<name>', 'option' )`).

| Field | Name | Type | Default | Effect |
| --- | --- | --- | --- | --- |
| Sites to include | `uams_searchwp_sites` | Checkboxes, one per site `get_sites()` returns whose details can be read, labeled `<site name> (ID: <id>)` | None in effect: the declared default is the main site's name, which is not one of the choice keys (site IDs) | The sites a search on the main site covers. See [Search behavior](Search-behavior.md#which-sites-are-searched). |
| Results per page | `uams_searchwp_results_per_page` | Number, 5 to 30, step 1 | 20 | Results on each page of search results. See [Search behavior](Search-behavior.md#results-per-page). |

The list of sites is read from the network when the field group is registered, so a site added to the network appears in the list on the next page load.

## Example

On a network whose main site is `Example Health` (ID 1) and which has a site `Example Clinic` (ID 4), ticking both boxes makes a search on the main site return results from both sites.
