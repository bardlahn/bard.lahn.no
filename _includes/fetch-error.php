<?php

// This snippet overwrites previously parsed page content with the content of an error page

// Checking if $serve_error is set and is a valid error (500 set as default)
$errors = ["400", "403", "404", "500"];
$serve_error = $serve_error ?? "500";
if (!in_array($serve_error, $errors)) {
    $serve_error = "500";
}

// Checking for error file to parse

$checkFile = $assets_path . $serve_error . ".".$lang.".md";
if (!file_exists($checkFile)) {
    $checkFile = $assets_path . $serve_error . ".md";
    if (!file_exists($checkFile)) {
        die("Error page file not found: " . $assets_path . $serve_error . ".md");
    }
}

// Parsing and passing on error page content

$parsedfile = parseMDFile($checkFile);
$content = $parsedfile['content'];
$fmatter = $parsedfile['frontmatter'];
$self_title = $fmatter['title'] ?? 'bard.lahn.no';
$self_type = PAGE_ERROR;

?>