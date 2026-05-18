<?php

// Building and printing meta tags for HTML HEAD section

$echo_pre = "\n    ";

$description = $fmatter['abstract'] ?? "Personal website of Bård Lahn: " . $self_title;
$description = $fmatter['description'] ?? $description;

if ($self_type != PAGE_ERROR) {

    // Printing page description
    echo $echo_pre . "<meta name=\"description\" content=\"" . $description . "\">";

} else {
    // If error page, printing nofollow
    echo $echo_pre . '<meta name="robots" content="noindex, nofollow">';
}

// Printing canonical URL
$canonical = $fmatter['routes']['canonical'] ?? ("/" . $self_url . "/");
echo $echo_pre . "<link rel=\"canonical\" href=\"" . $base_url . $canonical . "\">";

// Printing alternate language paths
foreach ($foundfiles as $lang_key => $file) {
    echo $echo_pre . '<link rel="alternate" hreflang="'
     . htmlspecialchars($lang_key) . '" href="'
     . $base_url . '/' . htmlspecialchars($lang_key)
     . '/' .  $self_url . '">';
}

// Beginning OpenGraph and Schema.org output

if ($self_type != PAGE_ERROR) {

    // Printing OpenGraph shared properties

    // Title - hardcoded title for bio page
    $echo_title = ($self_url == 'bio') ? '<meta property="og:title" content="Bård Lahn">' : '<meta property="og:title" content="' . $self_title . '">';
    echo $echo_pre . $echo_title;

    echo $echo_pre . '<meta property="og:description" content="' . $description . '">';
    echo $echo_pre . '<meta property="og:url" content="' . $base_url . '/' . $lang . $canonical . '">';
    echo $echo_pre . '<meta property="og:locale" content="' . $lang . '">';
    echo $echo_pre . '<meta property="og:site_name" content="Bård Lahn / bard.lahn.no">';

    if (isset($fmatter['date'])) {
        $dt = (new DateTime('now', new DateTimeZone('Europe/Oslo')))
            ->setTimestamp($fmatter['date']);
        $datetime = htmlspecialchars($dt->format(DateTime::ATOM));
    } else {
        $datetime = "";
    }

    $authors = getAuthors($fmatter['authors'] ?? null);

    if ($self_type == PAGE_MAIN) {

        // Printing OpenGraph website properties

        if ($self_url == 'bio') {
            // Hard coded profile metadata on bio page 
            echo $echo_pre . '<meta property="og:type" content="' . $echo_type . '">';
            echo $echo_pre . '<meta property="profile:first_name" content="Bård">';
            echo $echo_pre . '<meta property="profile:last_name"  content="Lahn">';
            echo $echo_pre . '<meta property="profile:username"   content="bardlahn">';
        } else {
            // General mainpage metadata
            echo $echo_pre . '<meta property="og:type" content="website">';
        }
        
    } elseif ($self_type == PAGE_SUB_BLOG) {

        // Printing OpenGraph article properties

        echo $echo_pre . '<meta property="og:type" content="article">';
        echo $echo_pre . '<meta property="article:published_time" content="' . $datetime . '">';

        // Printing author(s)
        foreach ($authors as $author) {
            if (!empty($author['url'])) {
                echo $echo_pre . '<meta property="article:author" content="' . htmlspecialchars($author['url']) . '">' . "\n";
            }
        }

    } elseif ($self_type == PAGE_SUB_PUB) {

        // Publication page type is handled as article in OpenGraph data,
        // unless pubtype is book, which is a separate og:type

        if (isset($fmatter['pub-data']['pubtype']) &&
            strtolower($fmatter['pub-data']['pubtype']) == 'book') {
            // Printing OpenGraph book properties
            echo $echo_pre . '<meta property="og:type" content="book">';
            echo $echo_pre . '<meta property="book:release_date" content="' . $datetime .'">';
            if (!empty($fmatter['pub-data']['isbn']))
                echo $echo_pre . '<meta property="book:isbn" content="' . $fmatter['pub-data']['isbn'] .'">';
            $pubtype = "book";
        } else {
            // Printing OpenGraph article properties
            echo $echo_pre . '<meta property="og:type" content="article">';
            echo $echo_pre . '<meta property="article:published_time" content="' . $datetime . '">';
            $pubtype = "article";
        }

        // Printing OpenGraph author(s) properties for publication
        foreach ($authors as $author) {
            if (!empty($author['url'])) {
                echo $echo_pre . '<meta property="' . $pubtype . ':author" content="' . htmlspecialchars($author['url']) . '">' . "\n";
            }
        }

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
  "potentialAction": {
    "@type": "SearchAction",
    "target": "https://example.com/search?q={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>

*/

?>


