<?php

// Configurable variables are hardcoded as of now

$site_title = "Bård Lahn";
$base_url = "https://bard.lahn.no";
$assets_rel_path = '/_assets/';
$self_profile_rel_path = '/bio/';


// Introducing public functions to fetch various configs

function getAuthors(mixed $raw): array {

    global $base_url;
    global $self_profile_rel_path;

    // 'self' author information is hardcoded - to be replaced by fetch from config YAML
    $self = [
        'familyName'    => 'Lahn',
        'givenName'     => 'Bård',
        'name'          => 'Bård Lahn',
        'url'           => $base_url . $self_profile_rel_path,
        'birthDate'     => '1983-05-26',
        'sameAs'        => 'https://orcid.org/0000-0001-9161-9455',
        'worksFor'      => [[
                '@type'         => 'Organization',
                'name'          => 'University of Oslo',
                'alternateName' => 'Universitetet i Oslo',
                'alternateName' => 'UiO',
                'url'           => 'https://www.uio.no'
                ]]
    ];

    // No authors defined — return false
    if (empty($raw)) {
        return false;
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
            $authors[$key] = $author;
            echo "<!-- DEBUG INFO: author key/content ". $authors[$key] . " / " . print_r($author) . " -->";
            // TO DO: Parse name into family and given names
            // TO DO: Error checking, ORCID -> URL, etc
        }
    }

    return $authors;
}

?>