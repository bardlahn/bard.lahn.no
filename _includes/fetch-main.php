<?php 

include $includes_path . 'md-parse.php';

$otherLang = $lang === 'en' ? 'no' : 'en';
$md_path = $assets_path; // Default Md path
$self_path = $self_url; // self_path defaults to self_url, but points to parent path of _sub pages

// Checks for markdown files to parse

if (empty($self_url)) {
    // Root request, checking for root files
    if (file_exists($root_path . 'index.' . $lang . '.md')) {
        $foundfile = $root_path . 'index.' . $lang . '.md';
        $md_path = $root_path;
    } elseif (file_exists($root_path . 'index.' . $otherLang . '.md')) {
        $foundfile = $root_path . 'index.' . $otherLang . '.md';
        $md_path = $root_path;
    }
} else {
    // Other request, checking for file
    if (file_exists($root_path . $self_url . '/index.' . $lang . '.md')) {
        $foundfile = $root_path . $self_url . '/index.' . $lang . '.md';
        $md_path = $root_path . $self_url . '/';
    } elseif (file_exists($root_path . $self_url . '/index.' . $otherLang . '.md')) {
        $foundfile = $assets_path . "otherLang." .$lang. ".md";
        $md_path = $root_path;
    } else {
        // Checking for subfile in directory _sub/
        $subpath = implode('/', array_slice($self_url_segments, 0, -1));
        $subname = end($self_url_segments);
        if ($subpath) {
            if ($subname) {
                if ($subname[0] != '_') { // Excluding files starting with underscore
                    $subfile = $root_path . $subpath . '/_sub/' . $subname;
                    if (file_exists($subfile . '.md')) {
                        $foundfile = $subfile . '.md';
                        $md_path = $root_path . $subpath . '/_sub/';
                        $self_path = $subpath;
                    } elseif (file_exists($subfile . '.' . $lang . '.md')) {
                        $foundfile = $subfile . '.' . $lang . '.md';
                        $md_path = $root_path . $subpath . '/_sub/';
                        $self_path = $subpath;
                    } elseif (file_exists($subfile . '.' . $otherLang . '.md')) {
                        $foundfile = $assets_path . "otherLang." .$lang. ".md";
                        $md_path = $root_path;
                    }
                }
            }
        }
    }

}

if (isset($foundfile)) {

    $parsedfile = parseMDFile($foundfile);

    // Checking for page type information
    // (PAGE_MAIN already default)

    if (isset($parsedfile['frontmatter']['type'])) {
        if ($parsedfile['frontmatter']['type']=='blog') {
            $self_type = PAGE_SUB_BLOG;
        } elseif ($parsedfile['frontmatter']['type']=='element') {
            $self_type = PAGE_SUB_ELEMENT;
        } elseif ($parsedfile['frontmatter']['type']=='resource') {
            // Parsed MD file is of type resource, not suitable for rendering
            $parsedfile = parseMDFile($assets_path . "500.".$lang.".md");
            $self_type = PAGE_ERROR;
        }
    }

    $content = $parsedfile;
    
} else {
    $content = parseMDFile($assets_path . "404.".$lang.".md");
    $self_type = PAGE_ERROR;
}

$self_title = $content['frontmatter']['title'] ?? 'bard.lahn.no';


?>