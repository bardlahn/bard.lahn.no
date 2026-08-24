<?php

/*

Bård Lahn / bard.lahn.no - personal website based on PHP and Markdown
---------------------------------------------------------------------

This is the main logic of the site, which serves all dynamic content.
This file (index.php) and the file _paths.php must be present in the root dir.

*/

// Setting up initial path config
include_once '_paths.php';

// Running initialisation
include_once $includes_path . "init.php";

// Fetching content
if (empty($serve_error)) include $includes_path . "fetch-main.php";

// Serving content
include $includes_path . "serve-main.php";

/*

GLOBALLY CONSISTENT VARIABLES
(Mostly available after init.php is included)

global $base_url;               // Base URL of site, no trailing slash
global $lang;                   // Language code of current language
global $lang_default;           // Default language code, as defined in config
global $lang_list;              // Array of available languages in the site, as defined in config
global $self_url;               // URL of current page, ex. site root address and leading/trailing /
global $self_url_segments;      // Array of self_url segments, split by /
global $self_type;              // Current page type, as set in Markdown (see constants in init.php)
global $self_profile_rel_path;  // Relative path of self profile page within the site structure
global $root_path;              // Absolute path to site root on the server, inc leading/trailing /
global $admin_path;             // Absolute path to root of admin page, inc leading/trailing /
global $includes_path;          // Absolute path to includes directory, inc leading/trailing /
global $assets_path;            // Absolute path to assets directory, inc leading/trailing /
global $config_path;            // Absolute path to config directory, inc leading/trailing /
global $assets_rel_path;        // Relative path to assets directory, inc leading/trailing /
global $lib_path;               // Absolute path to library directory, inc leading/trailing /

                                // Established in fetch-main.php:
global $md_path;                // Path of the placement of rendered MD file (including _sub if applicable)
global $self_path;              // Defaults to self_url, but points to parent path of _sub pages

AVAILABLE SHARED FUNCTIONS

getConfig(string $configfile, string $lang = '', string $element = ''): array / false
    // Established in init-functions.php
    // Used to fetch the frontmatter elements of $configfile in $config_path.
    // If $lang is set, checks for configfile with language suffix (defaults to no suffix).
    // If $element is set, returns only that element (with sub-elements) from config frontmatter.
    // Returns false on error.

getAuthors(mixed $raw): array / false
    // Established in init-functions.php
    // Used to fetch an array of author(s) for a given document 
    // based on the 'authors' element of the frontmatter.
    // Checks author tags against 'author' config file.
    // Returns false on error.

    logEvent(string $event, int $level = LOG_INFO): bool
    // Established in init-functions.php
    // Logs an event to the log file, with optional level.
    // Default level is LOG_INFO, other options are
    // LOG_WARNING and LOG_ERR.
    // Returns true on success, false on failure.

logEventHTML(string $event, int $level = LOG_INFO, bool $toFile = true): string
    // Established in init-functions.php
    // Logs an event to file using the logEvent function, and returns
    // a debug message formatted as a HTML comment.
    // Note: If $toFile is set to false, the event will not be logged to file, 
    // but the HTML comment will still be returned.

function statCountPath(string $path): bool
    // Established in init-functions.php
    // Calls the statistics service to add a hit for a specified path.

fetchSubEntries(string $mainpath, string $filter = '', string $sorting = ''): array
    // Established in fetch-sub.php
    // Fetches a list of pages/elements in the _sub directory of $mainpath,
    // based on the file _index.md in the _sub directory.

parseMDFile(string $filePath): array
    // Established in md-parse.php
    // Parses a given markdown file and returns the result in an array
    // consisting of ['frontmatter'] and ['content']

findIncludeFile(string $includefile): ?string
    // Established in md-parse.php
    // Parses a filename for a file to include.
    // Returns the full path to the file if it exists, else null.

getFrontmatterFields(string $filePath, array $fields): array
    // Established in md-parse.php
    // Helper function to extract specific frontmatter fields from a 
    // markdown file without fully parsing it

renderMDContent(string $text)
    // Established in md-render.php
    // Renders MD content as HTML and prints output directly.
    // Also handles custom codeblocks in MD content, given as
    // ::codeblock [args...]
    // [...further MD content...]
    // ::
    // (Available codeblocks are documented in md-render.php)

replaceVars(string $input): string
    // Established in md-render.php
    // Helper function to replace variables given in the MD content.
    // Variables can be inserted in MD on the form :$variable:
    // (Available variables are documented in md-render.php.)

*/

?>