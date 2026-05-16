<?php 

include $includes_path . 'md-parse.php';

$otherLang = $lang === 'en' ? 'no' : 'en';
$md_path = $assets_path; // Default Md path
$self_path = $self_url; // self_path defaults to self_url, but points to parent path of _sub pages

// Checks for markdown files to parse

$foundfiles = [];

if (empty($self_url)) {
    $search_path = $root_path;
} else {
    $search_path = $root_path . $self_url . '/';
}

if (is_dir($search_path)) {
    // Scan for index files in the given path
    foreach (glob($search_path . 'index.md') as $file) {
        $foundfiles['default'] = $file;
    }
    foreach (glob($search_path . 'index.*.md') as $file) {
        $basename = basename($file);
        // Extract the language segment between "index." and ".md"
        $lang_key = preg_replace('/^index\.(.+)\.md$/', '$1', $basename);
        $foundfiles[$lang_key] = $file;
    }
}

if (!empty($foundfiles)) {
    
    // Index file found
    // Setting md_path and moving on
    $md_path = $search_path;

} else {
    
    // No index file found
    // Checking _sub/ for a file matching the last URL segment
    $subpath = implode('/', array_slice($self_url_segments, 0, -1));
    $subname = end($self_url_segments);

    if ($subpath && $subname && $subname[0] !== '_') {
        $sub_dir = $root_path . $subpath . '/_sub/';

        foreach (glob($sub_dir . $subname . '.md') as $file) {
            $foundfiles['default'] = $file;
        }
        foreach (glob($sub_dir . $subname . '.*.md') as $file) {
            $basename = basename($file);
            $lang_key = preg_replace('/^' . preg_quote($subname, '/') . '\.(.+)\.md$/', '$1', $basename);
            $foundfiles[$lang_key] = $file;
        }

        if (!empty($foundfiles)) {
            $self_path = $subpath;
            $md_path = $sub_dir;
        }
    }
}

// Checking for found files to parse

if (empty($foundfiles)) {
    // Error handling: No file found
    $foundfile = null;
} elseif (isset($foundfiles[$lang])) {
    // First option: Found file with correct language
    $foundfile = $foundfiles[$lang];
} elseif (isset($foundfiles['default'])) {
    // Second option: Found file with no language set
    $foundfile = $foundfiles['default'];
} else {
    // Third option: File exists in other language
    $foundfile = $assets_path . "otherLang." .$lang. ".md";
}

if ($foundfile) {

    $parsedfile = parseMDFile($foundfile);

    // Checking for page type information
    // (PAGE_MAIN already default)

    if (isset($parsedfile['frontmatter']['type'])) {
        if ($parsedfile['frontmatter']['type']=='blog') {
            $self_type = PAGE_SUB_BLOG;
        } elseif ($parsedfile['frontmatter']['type']=='element') {
            $self_type = PAGE_SUB_ELEMENT;
        } elseif ($parsedfile['frontmatter']['type']=='publication') {
            $self_type = PAGE_SUB_PUB;
        } elseif ($parsedfile['frontmatter']['type']=='resource') {
            // Parsed MD file is of type resource, not suitable for rendering
            $parsedfile = parseMDFile($assets_path . "500.".$lang.".md");
            $self_type = PAGE_ERROR;
        }
    }

    // Fixing $foundfiles array so it can be iterated as a list of available langauges
    // (for use in meta tags)
    if (isset($parsedfile['frontmatter']['language']) && isset($foundfiles['default'])) {
        $foundfiles[$parsedfile['frontmatter']['language']] = $foundfiles['default'];
        unset($foundfiles['default']);
    }

    $content = $parsedfile;
    
} else {
    $content = parseMDFile($assets_path . "404.".$lang.".md");
    $self_type = PAGE_ERROR;
}

$self_title = $content['frontmatter']['title'] ?? 'bard.lahn.no';

?>