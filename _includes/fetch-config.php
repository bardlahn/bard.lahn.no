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

echo "<!-- DEBUG \n\n";

    // Array of authors
    $authors = [];
    foreach ($raw as $element) {

        if (is_array($element)) {
            $thisAuthor = key($element);
            if ($thisAuthor == 'self') {
                $authors['self'] = $self;
                echo "Found author in array: self \n";
            } elseif (is_array($element[$thisAuthor])) {
                $authors[$thisAuthor] = $element[$thisAuthor];
                echo "Found author: ". $thisAuthor . "\n";
                print_r($element[$thisAuthor]);
                // TO DO: Parse name into family and given names
                // TO DO: Error checking, ORCID -> URL, etc
            }
        } elseif ($element == 'self') {
            $authors['self'] = $self;
            echo "Found author as string: self \n";
        }  

    }

echo "\n\n-->";

    return $authors;
}

?>