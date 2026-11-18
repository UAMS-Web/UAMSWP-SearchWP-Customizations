# Search behavior

Written against v1.0.1.

The plugin changes SearchWP through four SearchWP filters. Each section below names the filter, what it does and the setting it reads. SearchWP's own documentation for multisite search is at [searchwp.com/documentation/multisite](https://searchwp.com/documentation/multisite/).

| Filter | Priority | Effect |
| --- | --- | --- |
| `searchwp\query\args` | 10 | Chooses the sites a search on the main site covers. |
| `searchwp\query\per_page` | 30 | Sets the number of results per page. |
| `searchwp\query\before` | 20 | When `?type=` is set, removes every post-type source but the one it names. |
| `searchwp\source\attribute\label` | 20 | Returns `Knowledge Articles` for each `post.kb` label the filter receives. |

## Which sites are searched

On the main site (site ID 1), a search covers the sites selected under **Sites to include** on the [Settings](Settings.md) page. When none are selected, it covers the main site alone. The plugin also sets SearchWP's `fields` argument to `default`, which its code comment describes as keeping the site each result came from.

**Note:** both settings are read only when ACF is loaded. Without ACF, a search on the main site covers the main site alone and shows 20 results per page. In v1.0.1 and earlier, the results-per-page filter read its setting without that check, so a search with ACF inactive failed with `Call to undefined function get_field()` (fixed by [UAMS-Web/UAMSWP-SearchWP-Customizations#72](https://github.com/UAMS-Web/UAMSWP-SearchWP-Customizations/issues/72)).

On any other site in the network, the plugin leaves SearchWP's query arguments unchanged.

## Results per page

Each page of results shows the number set under **Results per page** on the [Settings](Settings.md) page. When the field is empty or `0`, it shows 20.

**Note:** the 5 to 30 range is enforced by the field in the admin screen, not by the filter. A value stored some other way is used as it is.

## Narrowing results to one type

A search URL with a `type` query argument narrows the results to one post type. The value is a post type name, the part of the SearchWP source name after `post.`:

```text
https://example.org/?s=example&type=page
```

keeps the `post.page` source and removes every other post-type source from the engine the query uses. Without `type`, the engine is left as it is. A `type` that is empty (`?type=`), names no source, or is passed as an array (`?type[]=page`, read as empty) removes every post-type source.

The [Type filter links](Type-filter-links.md) function prints links that set this argument.

## The knowledge base label

For every label SearchWP passes through `searchwp\source\attribute\label` with the source `post.kb` (a post type named `kb`), the plugin returns `Knowledge Articles`. Labels for every other source are returned unchanged. Which labels SearchWP sends through this filter is SearchWP's behavior and is not described here.
