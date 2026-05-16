<?php

// Printing some debugging info - remove/change as appropriate

echo "\n";
echo "<!-- DEBUG self_url = ". $self_url . " -->\n";
echo "<!-- DEBUG self_type = ". $self_type . " -->\n";
echo "<!-- DEBUG self_path = ". $self_path . " -->\n";
echo "<!-- DEBUG md_path = ". $md_path . " -->\n";

// Building and printing meta tags for HTML HEAD section

$echo_pre = "\n    ";

$description = $content['frontmatter']['abstract'] ?? "Personal website of Bård Lahn: " . $self_title;
$description = $content['frontmatter']['description'] ?? $description;

if ($self_type != PAGE_ERROR) {

    // Printing page description
    echo $echo_pre . "<meta name=\"description\" content=\"" . $description . "\">";

} else {
    // If error page, printing nofollow
    echo $echo_pre . '<meta name="robots" content="noindex, nofollow">';
}

// Printing canonical URL
$canonical = $content['frontmatter']['routes']['canonical'] ?? ("/" . $self_url . "/");
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

    echo $echo_pre . '<meta property="og:title" content="' . $self_title . '">';
    echo $echo_pre . '<meta property="og:description" content="' . $description . '">';
    echo $echo_pre . '<meta property="og:url" content="' . $base_url . '/' . $lang . $canonical . '">';
    echo $echo_pre . '<meta property="og:locale" content="' . $lang . '">';
    echo $echo_pre . '<meta property="og:site_name" content="Bård Lahn / bard.lahn.no">';

    if (isset($content['frontmatter']['date'])) {
        $dt = (new DateTime('now', new DateTimeZone('Europe/Oslo')))
            ->setTimestamp($content['frontmatter']['date']);
        $datetime = htmlspecialchars($dt->format(DateTime::ATOM));
    } else {
        $datetime = "";
    }

    if ($self_type == PAGE_MAIN) {

        // Printing OpenGraph website properties

        echo $echo_pre . '<meta property="og:type" content="website">';
        
    } elseif ($self_type == PAGE_SUB_BLOG) {

        // Printing OpenGraph article properties

        echo $echo_pre . '<meta property="og:type" content="article">';
        echo $echo_pre . '<meta property="article:published_time" content="' . $datetime . '">';
        echo $echo_pre . '<meta property="article:modified_time" content="' . $datetime . '">';
        echo $echo_pre . '<meta property="article:author" content="' . $base_url . '/bio/">';

    } elseif ($self_type == PAGE_SUB_PUB) {

        if (isset($content['frontmatter']['pub-data']['type']) &&
            strtolower($content['frontmatter']['pub-data']['type']) == 'book') {

            // Printing OpenGraph book properties

            echo $echo_pre . '<meta property="og:type" content="book">';
            echo $echo_pre . '<meta property="book:release_date" content="' . $datetime .'">';

            if (!empty($content['frontmatter']['pub-data']['isbn']))
                echo $echo_pre . '<meta property="book:isbn" content="' . $content['frontmatter']['pub-data']['isbn'] .'">';

            foreach ($content['frontmatter']['pub-data']['authors'] ?? [] as $author) {
                if (!empty($author['url'])) {
                    echo $echo_pre . '<meta property="book:author" content="' . $author['url'] .'">';
                } else {
                    echo $echo_pre . '<meta property="book:author" content="' .  $base_url . '/bio/">';
                }
            }

        }

    }


}


/*

Schema.org:

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


