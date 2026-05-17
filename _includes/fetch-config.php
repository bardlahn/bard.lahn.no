<?php

function getAuthors(mixed $raw): array
global $base_url;
{
    $self = [
        'name-family'   => 'Lahn',
        'name-given'    => 'Bård',
        'url'           => $base_url . '/bio/',
    ];

    // No authors defined — return self as default
    if (empty($raw)) {
        return [$self];
    }

    // Single string value "self"
    if ($raw === 'self') {
        return [$self];
    }

    // Array of authors
    $authors = [];
    foreach ($raw as $key => $author) {
        if ($key === 'self') {
            $authors[] = $self;
        } else {
            $authors[] = [
                'name' => $author['name'] ?? '',
                'url'  => $author['url']  ?? '',
            ];
        }
    }

    return $authors;
}

// Usage:
$authors = resolveAuthors($content['frontmatter']['author'] ?? null);

// Template:
foreach ($authors as $author) {
    echo '<meta property="article:author" content="' . htmlspecialchars($author['url']) . '">' . "\n";
}

?>