# Search behavior

Written against v1.0.1.

The plugin changes SearchWP through four SearchWP filters. Each section below names the filter, what it does and the setting it reads. SearchWP's own documentation for multisite search is at [searchwp.com/documentation/multisite](https://searchwp.com/documentation/multisite/).

| Filter | Priority | Effect |
| --- | --- | --- |
| `searchwp\query\args` | 10 | Chooses the sites a search on the main site covers. |
| `searchwp\query\per_page` | 30 | Sets the number of results per page. |
| `searchwp\query\before` | 20 | Keeps only the engine source named by `?type=`. |
| `searchwp\source\attribute\label` | 20 | Returns `Knowledge Articles` for each `post.kb` label the filter receives. |

## Which sites are searched

On the main site (site ID 1), a search covers the sites ticked under **Sites to include** on the [Settings](Settings.md) page. When none are ticked, or when ACF is not active, it covers the main site alone. The plugin also asks SearchWP for its default result fields, so each result keeps the site it came from.

On any other site in the network, the plugin leaves the search as SearchWP built it: that site alone.

## Results per page

Each page of results shows the number set under **Results per page** on the [Settings](Settings.md) page. When the field is empty or `0`, it shows 20.

**Note:** the 5 to 30 range is enforced by the field in the admin screen, not by the filter. A value stored some other way is used as it is.

## Narrowing results to one type

A search URL with a `type` query argument returns results from one engine source only. The value is a post type name, the part of the SearchWP source name after `post.`:

```text
https://example.org/?s=example&type=page
```

keeps the `post.page` source and removes every other source from the engine the query uses. Without `type`, every source is searched. A `type` that names no source removes all of them, so the search returns nothing; so does a `type` passed as an array (`?type[]=page`), which is read as an empty value.

The [Type filter links](Type-filter-links.md) function prints links that set this argument.

## The knowledge base label

For every label SearchWP passes through `searchwp\source\attribute\label` with the source `post.kb` (a post type named `kb`), the plugin returns `Knowledge Articles`. Labels for every other source are returned unchanged. Which labels SearchWP sends through this filter is SearchWP's behavior and is not described here.
