<?php

// Setting general variables

include_once $includes_path . 'md-parse.php';
include_once $includes_path . 'fetch-config.php';

// Fetching core site settings

$site_config = getConfig('site');

if ($site_config) {

    $site_title             = $site_config['site-title'];
    $base_url               = $site_config['site-url'];
    $assets_rel_path        = $site_config['paths']['assets-rel'];
    $self_profile_rel_path  = $site_config['paths']['profile-rel'];

    $lang_list = [];
    foreach ($site_config['languages'] as $code => $lang) {
        $lang_list[strtolower($code)] = $lang['name'];
    }

    $lang_default = !empty($site_config['language-default']) ? strtolower($site_config['language-default']) : array_keys[$lang_list][0];

} else {
    $serve_error = 500;
    include $includes_path . 'fetch-error.php';
}

// Setting language based on browser check (defaults to "en")
$browserLang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2));
$lang = in_array($browserLang, array_keys($lang_list)) ? $browserLang : $lang_default;

// Retrieving requested URL, resets language based on URL
$self_url_segments = array_values(array_filter(explode('/', trim(strtok($_SERVER['REQUEST_URI'] ?? '', '?'), '/'))));
if (in_array($self_url_segments[0] ?? '', array_keys($lang_list))) {
    $lang = strtolower($self_url_segments[0]);
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

// Redirecting to error page if URL starts with "error"

if ($self_url_segments[0] ?? '' == 'error') {
    echo "debug: We are here with " . $self_url_segments[0];
    echo "\ndebug: Next segment is " . $self_url_segments[1];
    echo "\ndebug: SERVER redirect is " . $_SERVER['REDIRECT_STATUS'] ?? '';
    $self_type = PAGE_ERROR;
    $serve_error = $self_url_segments[1] ?? "500";
    include $includes_path . 'fetch-error.php';
}

?>