<?php

$assets_rel_path = '/_assets/';

// Sets language based on browser check (defaults to "en")
$browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2);
$lang = in_array($browserLang, ['no', 'en']) ? $browserLang : 'en';

// Retrieves requested URL, sets language based on URL
$self_url_segments = array_values(array_filter(explode('/', trim(strtok($_SERVER['REQUEST_URI'] ?? '', '?'), '/'))));
if (in_array($self_url_segments[0] ?? '', ['en', 'no'])) {
    $lang = $self_url_segments[0];
    array_shift($self_url_segments);
}
$self_url = implode('/', $self_url_segments);

// Defining page types (default is "main")

define (    "PAGE_MAIN",        "main");
define (    "PAGE_ERROR",       "error");
define (    "PAGE_SUB_BLOG",    "blog");
define (    "PAGE_SUB_ELEMENT", "element");

$self_type = PAGE_MAIN;

?>