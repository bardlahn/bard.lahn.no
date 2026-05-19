<?php

// Building and printing meta tags for HTML HEAD section

$echo_pre = "\n    ";
$schemaJson['@context']  = 'https://schema.org';

$meta_desc = $fmatter['abstract'] ?? "Personal website of Bård Lahn: " . $self_title;
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

    // Title (hardcoded title for bio page)
    $meta_title = ($self_url == trim($self_profile_rel_path, '/')) ? '<meta property="og:title" content="Bård Lahn">' : '<meta property="og:title" content="' . $self_title . '">';
    
    echo $echo_pre . $meta_title;

    echo $echo_pre . '<meta property="og:description" content="' . $meta_desc . '">';
    echo $echo_pre . '<meta property="og:url" content="' . $base_url . '/' . $lang . $meta_canonical . '">';
    echo $echo_pre . '<meta property="og:locale" content="' . $lang . '">';
    echo $echo_pre . '<meta property="og:site_name" content="Bård Lahn / bard.lahn.no">';

    if (isset($fmatter['date'])) {
        $dt = (new DateTime('now', new DateTimeZone('Europe/Oslo')))
            ->setTimestamp($fmatter['date']);
        $meta_date = htmlspecialchars($dt->format(DateTime::ATOM));
    } else {
        $meta_date = "";
    }

    $meta_authors = getAuthors($fmatter['authors'] ?? null);

    if ($self_type == PAGE_MAIN) {

        if ($self_url == trim($self_profile_rel_path, '/')) {

            // Returning profile metadata for designated profile page

            echo $echo_pre . '<meta property="og:type" content="profile">';
            echo $echo_pre . '<meta property="profile:first_name" content="Bård">';
            echo $echo_pre . '<meta property="profile:last_name"  content="Lahn">';
            echo $echo_pre . '<meta property="profile:username"   content="bardlahn">';

            // Adding Schema.org person properties

            $meta_worksfor = [
                '@type'         => 'Organization',
                'name'          => 'University of Oslo',
                'alternateName' => 'Universitetet i Oslo',
                'url'           => 'https://www.uio.no'
                ];

            $schemaJson['@type']        = 'Person';
            $schemaJson['familyName']   = 'Lahn';
            $schemaJson['givenName']    = 'Bård';
            $schemaJson['birthDate']    = '1983-05-26';
            $schemaJson['jobTitle']     = 'Associate Professor';
            $schemaJson['url']          = $base_url . '/' . $lang . $meta_canonical;
            $schemaJson['sameAs']       = 'https://orcid.org/0000-0001-9161-9455';
            $schemaJson['worksFor'][]   = $meta_worksfor;

        } else {
            
            // General mainpage metadata

            echo $echo_pre . '<meta property="og:type" content="website">';

            // Adding Schema.org webpage properties
            $schemaJson['@type']         = 'WebPage';
            $schemaJson['headline']      = $self_title;
            $schemaJson['url']           = $base_url . '/' . $lang . $meta_canonical;

        }
        
    } elseif ($self_type == PAGE_SUB_BLOG) {

        // Printing OpenGraph article properties

        echo $echo_pre . '<meta property="og:type" content="article">';
        echo $echo_pre . '<meta property="article:published_time" content="' . $meta_date . '">';

        // Adding Schema.org article properties

        $schemaJson['@type']            = 'Article';
        $schemaJson['headline']         = $self_title;
        $schemaJson['datePublished']    = $meta_date;
        $schemaJson['abstract']         = $meta_desc;
        $schemaJson['url']              = $base_url . '/' . $lang . $meta_canonical;

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
            echo $echo_pre . '<meta property="book:release_date" content="' . $meta_date .'">';
            if (!empty($fmatter['pub-data']['isbn']))
                echo $echo_pre . '<meta property="book:isbn" content="' . $fmatter['pub-data']['isbn'] .'">';
            $pubtype = "book";

            // Adding Schema.org book properties
            $schemaJson['@type'] = 'Book';

        } else {
            
            // Printing OpenGraph article properties
            echo $echo_pre . '<meta property="og:type" content="article">';
            echo $echo_pre . '<meta property="article:published_time" content="' . $meta_date . '">';
            $pubtype = "article";

            // Adding Schema.org publication properties
            $schemaJson['@type'] = 'ScholarlyArticle'; // or Report, Thesis

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

        $jsonLD = json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n\n<script type=\"application/ld+json\">\n" . $jsonLD . "\n</script>\n";

    }

}


/*

Schema.org - yet to be implemented:

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "Your Site Name",
  "url": "https://example.com/",
}
</script>

*/

?>


