<?php

function getAuthors(mixed $raw): array {

    global $base_url;
    global $self_profile_rel_path;

    $self = [
        'name-family'   => 'Lahn',
        'name-given'    => 'Bård',
        'url'           => $base_url . $self_profile_rel_path,
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
                // TO DO: Parse name into family and given names
                'name' => $author['name'] ?? '',
                'url'  => $author['url']  ?? '',
            ];
        }
    }

    return $authors;
}

?>