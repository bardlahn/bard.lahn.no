<?php


// Configurable variables are hardcoded as of now

$site_title = "Bård Lahn";
$base_url = "https://bard.lahn.no";
$assets_rel_path = '/_assets/';
$self_profile_rel_path = '/bio/';


// Introducing public functions to fetch various configs

function getConfig(string $configfile, string $lang = '', string $element = '') {

    // Fetching config from frontmatter of specified file.
    // If $lang is set, checks for filename containing language suffix.
    // If $element is set, returns only the specified frontmatter (top-level) element.

    global $includes_path;
    global $config_path;
    include_once $includes_path . 'md-parse.php';
    $file = $config_path . $configfile . '.md';
    if (!empty($lang)) {
        // $lang is set - checking if language-specific file exists
        $check = $config_path . $configfile . '.' . $lang . '.md';
        $file = (file_exists($check)) ? $check : $file;
    }
    if (file_exists($file)) {
        // File exists - parses and returns
        $config = parseMDFile($file);
        if (!empty($element) && isset($config['frontmatter'][$element])) {
            // $element is set - returning only specified element
            return $config['frontmatter'][$element];
        } else {
            // Returning full frontmatter
            return $config['frontmatter'];
        }
    } else {
        // Config file does not exist
        return false;
    }

}

function getAuthors(mixed $raw): mixed {

    // Assembling author list from frontmatter, returning as an array

    // Fetching predefined authors from config file
    $authConfig = getConfig('authors', element: 'authors');
    if (!$authConfig) {
        return false;
    }

    // $authConfig = array_merge(...array_values($authConfig));

    if (!isset($authConfig['self'])) {
        $authConfig['self'] = ['name' => '(Author-name SELF not set)', 'url' => '(Author URL SELF not set)'];
    }

    // No authors defined — return false
    if (empty($raw)) {
        return false;
    }

    // Single string value "self"
    if ($raw === 'self') {
        return ['self' => $authConfig['self']];
    }

    // Array of authors
    $authors = [];
    foreach ($raw as $element) {

        if (is_array($element)) {
            $thisAuthor = key($element);
            echo "<!-- DEBUG: IS ARRAY - " . $thisAuthor . " -->\n";
            $authors[$thisAuthor] = $element[$thisAuthor];
        } else {
            echo "<!-- DEBUG: IS NOT ARRAY - " . $element . " -->\n";
            if (isset($authConfig[$element])) {
                if (isset($authConfig[$element]['sameAs'])) {
                    $authConfig[$element]['sameAs']['@type'] = "Organization";
                }
                $authors[$element] = $authConfig[$element];
            } else {
                $authors[$element] = [
                    'name' => '(Author-name '.$element.' not set in config file)', 
                    'url' => '(Author URL '.$element.' not set in config file)'
                    ];
            }
        }

        // TO DO: Parse name into family and given names

        if (isset($authors[$thisAuthor]['orcid'])) {
            $authors[$thisAuthor]['sameAs'] = 'https://orcid.org/' . $authors[$thisAuthor]['orcid'];
            $authors[$thisAuthor]['url'] = (empty($authors[$thisAuthor]['url'])) ?
                $authors[$thisAuthor]['sameAs'] :
                $authors[$thisAuthor]['url'];
        }

    }

    return $authors;
}

?>