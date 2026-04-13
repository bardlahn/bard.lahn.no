<?php

function fetchSubEntries(string $mainpath, string $filter = '', string $sorting = ''): array {
    $parsed = parseMDFile($mainpath . '/_sub/_index.md');
    $frontmatter = $parsed['frontmatter'];

    if ($frontmatter['sub_type'] == PAGE_SUB_BLOG) {

        $rawItems = $frontmatter['sub_items'] ?? [];
        $entries = [];

        if (is_array($rawItems)) {
            foreach ($rawItems as $item) {
                if (!is_array($item)) continue;
                foreach ($item as $slug => $fields) {
                    if (!is_array($fields)) continue;
                    $entries[] = array_merge(
                        ['slug' => $slug],
                        array_map(fn($v) => is_int($v) ? (new \DateTime())->setTimestamp($v) : $v, $fields)
                    );
                }
            }
        }

        // Filtering
        if ($filter !== '') {
            $filters = array_map(fn($f) => explode('=', trim($f), 2), explode(',', $filter));

            $entries = array_filter($entries, function($entry) use ($filters) {
                foreach ($filters as [$filterKey, $filterValue]) {
                    $match = match($filterKey) {
                        'year'     => isset($entry['date'])       && $entry['date']->format('Y') === $filterValue,
                        'tag'      => isset($entry['tags'])       && in_array($filterValue, $entry['tags']),
                        'category' => isset($entry['categories']) && in_array($filterValue, $entry['categories']),
                        'lang'     => isset($entry['language'])   && strtolower($entry['language']) === strtolower($filterValue),
                        default    => true,
                    };
                    if (!$match) return false;
                }
                return true;
            });
        }
        
        // Sorting
        if (empty($sorting)) {
            $sorting = $frontmatter['sub_sort'] ?? 'date=descending';
        }
        usort($entries, function($a, $b) use ($sorting) {
            [$sortKey, $sortDir] = explode('=', $sorting, 2);

            $valA = $a[$sortKey] ?? '';
            $valB = $b[$sortKey] ?? '';

            $cmp = match($sortKey) {
                'date'  => $valA <=> $valB,
                'title' => strcasecmp((string)$valA, (string)$valB),
                default => 0,
            };

            return $sortDir === 'ascending' ? $cmp : -$cmp;
        });

        return array_merge(
            array_diff_key($frontmatter, ['sub_items' => null]),
            ['sub_items' => array_values($entries)]
        );

    } else {
        // Sub-type is not blog!
    }

}


?>