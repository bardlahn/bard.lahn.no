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

    // Fetching predefined authors from config file and processing them
    $config = getConfig('authors', element: 'authors');
    if (!$config) {
        return false;
    }
    $config = array_merge(...array_values($config));
    $authConfig = [];
    foreach ($config as $aC) {
        if (isset($aC['worksFor'])) {
            $aC['worksFor']['@type'] = "Organization";
        }
        if (isset($aC['orcid'])) {
            $aC['sameAs'] = $aC['sameAs'] ?? 'https://orcid.org/' . $aC['orcid'];
            $aC['url'] = $aC['url'] ?? $aC['sameAs'];
        }
        $authConfig[] = $aC;
    }

    if (!isset($authConfig['self'])) {
        $authConfig['self'] = ['name' => "('self' name not set)", 'url' => "('self' URL not set)"];
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
            $authors[$thisAuthor] = $element[$thisAuthor];
        } else {
            $thisAuthor = $element;
            if (isset($authConfig[$thisAuthor])) {
                $authors[$thisAuthor] = $authConfig[$thisAuthor];
            } else {
                $authors[$thisAuthor] = [
                    'name' => "('".$thisAuthor."' name not set)", 
                    'url' => "('".$thisAuthor."' url not set)"
                    ];
            }
        }

        // TO DO: Parse name into family and given names

        if (isset($authors[$thisAuthor]['orcid'])) {
            $authors[$thisAuthor]['sameAs'] = 'https://orcid.org/' . $authors[$thisAuthor]['orcid'];
            $authors[$thisAuthor]['url'] = $authors[$thisAuthor]['url'] ?? $authors[$thisAuthor]['sameAs'];
        }

    }

    return $authors;
}

?>