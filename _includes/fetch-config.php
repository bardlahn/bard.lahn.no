<?php

$site_title = "Bård Lahn";
$base_url = "https://bard.lahn.no";
$assets_rel_path = '/_assets/';
$self_profile_rel_path = '/bio/';

function getAuthors(mixed $raw): array {

    global $base_url;
    global $self_profile_rel_path;

    $self = [
        'familyName'    => 'Lahn',
        'givenName'     => 'Bård',
        'name'          => 'Bård Lahn',
        'url'           => $base_url . $self_profile_rel_path,
        'birthDate'     => '1983-05-26',
        'sameAs'        => 'https://orcid.org/0000-0001-9161-9455',
        'worksFor'      => [
                '@type'         => 'Organization',
                'name'          => 'University of Oslo',
                'alternateName' => 'Universitetet i Oslo',
                'alternateName' => 'UiO',
                'url'           => 'https://www.uio.no'
                ]
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
                // TO DO: Automatic incorporation of all entries
                'name' => $author['name'] ?? '',
                'url'  => $author['url']  ?? '',
            ];
        }
    }

    return $authors;
}

?>