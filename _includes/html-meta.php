<?php

// Building and printing meta tags for HTML HEAD section

$echo_pre = "\n    ";
$schemaJson['@context']  = 'https://schema.org';

$meta_desc = $fmatter['abstract'] ?? $self_title . ' / ' . $site_title;
$meta_desc = $fmatter['description'] ?? $meta_desc;

if ($self_type != PAGE_ERROR) {

    // Printing page description
    echo $echo_pre . "<meta name=\"description\" content=\"" . $meta_desc . "\">";

} else {
    // If error page, printing nofollow
    echo $echo_pre . '<meta name="robots" content="noindex, nofollow">';
}

// Printing canonical URL
$meta_canonical = $fmatter['routes']['canonical'] ?? ("/" . $self_url . "/");
echo $echo_pre . "<link rel=\"canonical\" href=\"" . $base_url . $meta_canonical . "\">";

// Printing alternate language paths
foreach ($foundfiles as $lang_key => $file) {
    echo $echo_pre . '<link rel="alternate" hreflang="'
     . htmlspecialchars($lang_key) . '" href="'
     . $base_url . '/' . htmlspecialchars($lang_key)
     . '/' .  $self_url . '">';
}

// Beginning OpenGraph and Schema.org output

if ($self_type != PAGE_ERROR) {

    // Adding general metadata for all pages

    echo $echo_pre . '<meta property="og:description" content="' . $meta_desc . '">';
    echo $echo_pre . '<meta property="og:url" content="' . $base_url . '/' . $lang . $meta_canonical . '">';
    echo $echo_pre . '<meta property="og:locale" content="' . $lang . '">';
    echo $echo_pre . '<meta property="og:site_name" content="'.$site_title.'">';

    if (isset($fmatter['date'])) {
        $dt = (new DateTime('now', new DateTimeZone('Europe/Oslo')))
            ->setTimestamp($fmatter['date']);
        $meta_date = htmlspecialchars($dt->format(DateTime::ATOM));
    } else {
        $meta_date = "";
    }

    $meta_authors = getAuthors($fmatter['authors'] ?? null);

    if ($self_type == PAGE_MAIN) {

        if ($self_url == '') {

            // Root page - returning website metadata

            echo $echo_pre . '<meta property="og:type" content="website">';
            echo $echo_pre . '<meta property="og:title" content="' . $self_title . ' / ' . $site_title . '">';

            // Adding Schema.org webpage properties
            $schemaJson['@type']         = 'WebPage';
            $schemaJson['headline']      = $self_title;

        } elseif ($self_url == trim($self_profile_rel_path, '/')) {

            // Profile page - returning profile metadata

            echo $echo_pre . '<meta property="og:type" content="profile">';
            echo $echo_pre . '<meta property="og:title" content="' . $meta_authors['self']['name'] . '">';
            echo $echo_pre . '<meta property="profile:first_name" content="'.$meta_authors['self']['familyName'].'">'; // Add error handling
            echo $echo_pre . '<meta property="profile:last_name"  content="'.$meta_authors['self']['givenName'].'">';  // Add error handling

            // Adding Schema.org person properties
            // (TO DO: Automate)

            $schemaJson['@type']        = 'Person';
            $schemaJson['name']         = $meta_authors['self']['name'];
            $schemaJson['familyName']   = $meta_authors['self']['familyName'];
            $schemaJson['givenName']    = $meta_authors['self']['givenName'];
            $schemaJson['birthDate']    = $meta_authors['self']['birthDate'];
            $schemaJson['url']          = $meta_authors['self']['url'];
            $schemaJson['sameAs']       = $meta_authors['self']['sameAs'];
            $schemaJson['worksFor'][]   = $meta_authors['self']['worksFor'];

        } else {
            
            // General mainpage - returning web page metadata

            echo $echo_pre . '<meta property="og:type" content="website">';
            echo $echo_pre . '<meta property="og:title" content="' . $self_title . ' / ' . $site_title . '">';

            // Adding Schema.org webpage properties
            $schemaJson['@type']         = 'WebPage';
            $schemaJson['headline']      = $self_title;

        }
        
    } elseif ($self_type == PAGE_SUB_BLOG) {

        // Printing OpenGraph article properties

        echo $echo_pre . '<meta property="og:type" content="article">';
        echo $echo_pre . '<meta property="og:title" content="' . $self_title . '">';
        echo $echo_pre . '<meta property="article:published_time" content="' . $meta_date . '">';

        // Adding Schema.org article properties

        $schemaJson['@type']            = 'Article';
        $schemaJson['headline']         = $self_title;
        $schemaJson['datePublished']    = $meta_date;
        $schemaJson['abstract']         = $meta_desc;

        // Printing author(s)
        foreach ($meta_authors as $author) {
            if (!empty($author['url'])) {
                echo $echo_pre . '<meta property="article:author" content="' . htmlspecialchars($author['url']) . '">' . "\n";
                $schemaJson['author'][] = [
                    '@type' => 'Person',
                    'name'  => $author['name'],
                    'url'   => $author['url']
                    ];
            }
        }

    } elseif ($self_type == PAGE_SUB_PUB) {

        // Publication page type is handled as article in OpenGraph data,
        // unless pubtype is book, which is a separate og:type

        if (isset($fmatter['pub-data']['pubtype']) &&
            strtolower($fmatter['pub-data']['pubtype']) == 'book') {

            // Printing OpenGraph book properties
            echo $echo_pre . '<meta property="og:type" content="book">';
            echo $echo_pre . '<meta property="og:title" content="' . $self_title . '">';
            echo $echo_pre . '<meta property="book:release_date" content="' . $meta_date .'">';
            if (!empty($fmatter['pub-data']['isbn']))
                echo $echo_pre . '<meta property="book:isbn" content="' . $fmatter['pub-data']['isbn'] .'">';
            $pubtype = "book";

            // Adding Schema.org book properties
            $schemaJson['@type']         = 'Book';
            $schemaJson['name']          = $self_title;
            $schemaJson['isbn']          = $fmatter['pub-data']['isbn'] ?? '';
            $schemaJson['datePublished'] = $meta_date;

        } else {
            
            // Printing OpenGraph article properties
            echo $echo_pre . '<meta property="og:type" content="article">';
            echo $echo_pre . '<meta property="og:title" content="' . $self_title . '">';
            echo $echo_pre . '<meta property="article:published_time" content="' . $meta_date . '">';
            $pubtype = "article";

            // Adding Schema.org publication properties
            $schemaJson['@type'] = 'ScholarlyArticle'; // TO DO: Implement Report, Thesis, (book section)

        }

        // Printing OpenGraph author(s) properties for publication
        foreach ($meta_authors as $author) {
            if (!empty($author['url'])) {
                echo $echo_pre . '<meta property="' . $pubtype . ':author" content="' . htmlspecialchars($author['url']) . '">' . "\n";
            }
        }

    }

    if (isset($schemaJson['@type'])) {

        // Schema.org type is set - proceeding to printing JSON-LD script

        $schemaJson['url'] = $schemaJson['url'] ?? $base_url . '/' . $lang . $meta_canonical;

        $jsonLD = json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n\n<script type=\"application/ld+json\">\n" . $jsonLD . "\n</script>\n";

    }

}

?>


