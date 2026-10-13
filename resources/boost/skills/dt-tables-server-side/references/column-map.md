# The Column Map

`columnMap()` tells the package which SQL each row key is searched and sorted by. It is the only link between what the browser asks for and what the database can do.

## Entry Forms

```php
public function columnMap(): array
{
    return [
        //  A SQL expression: searched and sorted by it
        'customer_name' => 'customers.name',

        //  A closure: builds its own search condition (not sortable on its own)
        'total' => fn ($query, $term) => is_numeric($term)
            ? $query->where('orders.total', '>=', (float) $term)
            : $query->whereRaw('1 = 0'),

        //  The array form sets search, sort and the global opt-out apart
        'status' => [
            'search' => 'orders.status',
            'sort' => 'orders.status',
            'global' => false,
        ],
    ];
}
```

| Entry | Searchable | Sortable |
|-------|------------|----------|
| `'key' => 'table.column'` | yes | yes |
| `'key' => fn ($query, $term) => ...` | yes | no |
| `['search' => 'expr', ...]` | yes | yes, by `sort` or else by the `search` expression |
| `['search' => Closure, 'sort' => 'expr']` | yes | yes |
| `['sort' => 'expr']` | no | yes |
| key left out | no | no |

A string that is not a plain `table.column` name is used as raw SQL, for example `"case when orders.id % 2 = 0 then 'Pending' else 'Completed' end"`. It comes from your code, never from the browser, so it is not escaped.

## What the Browser Is Told

The component reads `columnMap()` once and sends the browser two lists, `searchable` and `orderable`. The JS then:

- sets `orderable: false` / `searchable: false` on every other column, so no sort arrow is drawn;
- skips creating the filter box or select for a column the server cannot search, even if its `resources/data-tables/{key}.json` entry asks for one;
- never shows an arrow on `dtt-row-index` or `dtt-actions` columns, which are not in the map.

A request on an unmapped key is silently ignored by the server as well.

## Searching

- **Global search** (the box above the table): the text is split into words with DataTables' smart-search rules. Every word must match at least one searchable column (AND across words, OR across columns). Matching is case-insensitive and contains-style.
  - `"exact phrase"` in quotes matches as a whole.
  - `!word` excludes rows containing the word.
  - `%` and `_` are searched literally, not as wildcards.
- **Column filters**: an `input` filter contains-matches its words in that one column. A `select` filter matches the whole value, case-insensitively, against the option's stored value.
- A text column compiles to `lower(coalesce(cast(col as char), '')) like ? escape '!'` (the cast type depends on the driver).
- A **closure** receives each word typed in the global box as `$term`, so it must `whereRaw('1 = 0')` for words it cannot interpret and let other columns decide.
- `'global' => false` keeps a column out of the global search while its own filter still works. Use it on ids, amounts and status codes.

## Ordering

- The browser's sort applies, one expression per sorted column, in priority order.
- When the browser has not sorted, the order from `query()` applies.
- `keyColumn()` is always appended last so paging is stable, even across ties.
- Sorting by a column from a joined table must sort the whole joined set before taking a page, so it is the slowest request. Index the join keys.

## Counts

`recordsFiltered` is an exact count after searching. `recordsTotal` is an exact count without searching; when nothing is being searched one count serves for both. Counts use the same joins as the query, so they cost about as much as a page.

## Keeping the Map and `handle()` in Step

The page is chosen in SQL and shaped in PHP. If the map says a status is `case when orders.id % 2 = 0 then 'Pending' ...` then `handle()` must derive the displayed status from the same row data. Searching "Pending" would otherwise return rows labelled "Completed".

## Performance Notes

- A leading-wildcard `like '%word%'` cannot use an index. Keep the global search to the columns people actually search.
- Index the foreign keys used in joins and the columns used in default and filter conditions.
- A composite index such as `(tenant_id, amount)` speeds page loads, amount sorts and exact filters.
- FULLTEXT only helps a column on the driving table, not an OR across several joined tables.
