<?php

/*

Bård Lahn / bard.lahn.no - personal website based on PHP and Markdown
---------------------------------------------------------------------

This is the main logic of the site, which serves all dynamic content.
This file (index.php) and the file _paths.php must be present in the root dir.

*/

// Setting up initial path config
include '_paths.php';

// Running initialisation
include $includes_path . "init.php";

// Fetching content
include $includes_path . "fetch-main.php";

// Serving content
include $includes_path . "serve-main.php";

/*

GLOBALLY CONSISTENT VARIABLES
(Mostly available after init.php is included)

global $base_url;               // Base URL of site, no trailing slash
global $lang;                   // Language code of current language
global $otherLang;              // Langauge code of other available language
global $self_url;               // URL of current page, ex. site root address and leading/trailing /
global $self_url_segments;      // Array of self_url segments, split by /
global $self_type;              // Current page type, as set in Markdown (see constants in init.php)
global $self_profile_rel_path;  // Relative path of self profile page within the site structure
global $root_path;              // Absolute path to site root on the server, inc leading/trailing /
global $admin_path;             // Absolute path to root of admin page, inc leading/trailing /
global $includes_path;          // Absolute path to includes directory, inc leading/trailing /
global $assets_path;            // Absolute path to assets directory, inc leading/trailing /
global $assets_rel_path;        // Relative path to assets directory, inc leading/trailing /
global $lib_path;               // Absolute path to library directory, inc leading/trailing /


AVAILABLE SHARED FUNCTIONS

getAuthors(mixed $raw): array
    // Established in fetch-config.php
    // Used to fetch an array of author(s) for a given document 
    // based on the 'authors' element of the frontmatter.
    // Always returns the default (self) author, in addition to others
    // given in the frontmatter.

fetchSubEntries(string $mainpath, string $filter = '', string $sorting = ''): array
    // Established in fetch-sub.php
    // Fetches a list of pages/elements in the _sub directory of $mainpath,
    // based on the file _index.md in the _sub directory.

parseMDFile(string $filePath): array
    // Established in md-parse.php
    // Parses a given markdown file and returns the result in an array
    // consisting of ['frontmatter'] and ['content']

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