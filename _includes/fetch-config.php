<?php

function getAuthors(mixed $raw): array {

    global $base_url;
    global $self_profile_rel_path;

    $self = [
        'name-family'   => 'Lahn',
        'name-given'    => 'Bård',
        'name-full'     => 'Bård Lahn',
        'url'           => $base_url . $self_profile_rel_path,
        'birth-date'    => '1983-05-26',
        'orcid'         => 'https://orcid.org/0000-0001-9161-9455'
    ];

    // No authors defined — return self as default
    if (empty($raw)) {
        return ['self' => $self];
    }

    // Single string value "self"
    if ($raw === 'self') {
        return ['self' => $self];
    }

    // Array of authors
    $authors = [];
    foreach ($raw as $key => $author) {
        if ($key === 'self') {
            $authors['self'] = $self;
        } else {
            $authors[$key] = [
                // TO DO: Parse name into family and given names
                'name' => $author['name'] ?? '',
                'url'  => $author['url']  ?? '',
            ];
        }
    }

    return $authors;
}

?>