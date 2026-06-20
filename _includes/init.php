<?php

// Setting general variables

include_once $includes_path . 'md-parse.php';
include_once $includes_path . 'fetch-config.php';

// Retrieving requested URL, resets language based on URL
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
define (    "PAGE_SUB_PUB",     "publication");

$self_type = PAGE_MAIN;

// Fetching core site settings

$site_config = getConfig('site');

if ($site_config) {

    $site_title             = $site_config['site-title'];
    $base_url               = $site_config['site-url'];
    $assets_rel_path        = $site_config['paths']['assets-rel'];
    $self_profile_rel_path  = $site_config['paths']['profile-rel'];

    $lang_list = [];
    foreach ($site_config['languages'] as $code => $lang) {
        $lang_list[$code] = $lang['name'];
    }

} else {
    $serve_error = 500;
    include $includes_path . 'fetch-error.php';
}

// Setting language based on browser check (defaults to "en")
$browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2);
$lang = in_array($browserLang, ['no', 'en']) ? $browserLang : 'no';             // TO DO: REPLACE WITH SITE CONFIG CHECK
$otherLang = $lang === 'en' ? 'no' : 'en';                                      //   AND MAKE $otherLang REDUNDANT


?>