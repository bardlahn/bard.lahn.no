<?php

// Printing some debugging info - remove/change as appropriate

echo "\n";
echo "<!-- DEBUG self_url = ". $self_url . " -->\n";
echo "<!-- DEBUG self_type = ". $self_type . " -->\n";
echo "<!-- DEBUG self_path = ". $self_path . " -->\n";
echo "<!-- DEBUG md_path = ". $md_path . " -->\n";

// Building and printing meta tags for HTML HEAD section

$echo_pre = "\n    ";

if ($self_type != PAGE_ERROR) {

    // Printing page description
    $description = $content['frontmatter']['abstract'] ?? "Personal website of Bård Lahn: " . $self_title;
    $description = $content['frontmatter']['description'] ?? $description;
    echo $echo_pre . "<meta name=\"description\" content=\"" . $description . "\">";

} else {
    // If error page, printing nofollow
    echo ($self_type == PAGE_ERROR) ? $echo_pre . '<meta name="robots" content="noindex, nofollow">' : '';
}

// Printing canonical URL
$canonical = $content['frontmatter']['routes']['canonical'] ?? ($self_url . "/");
echo $echo_pre . "<link rel=\"canonical\" href=\"" . $base_url . $canonical . "\">";

// Printing alternate language paths
foreach ($foundfiles as $lang_key => $file) {
    echo $echo_pre . '<link rel="alternate" hreflang="'
     . htmlspecialchars($lang_key) . '" href="'
     . $base_url . '/' . htmlspecialchars($lang_key)
     . '/' .  $self_url . '">';
}


/*

YET TO IMPLEMENT

OpenGraph:

<meta property="og:title" content="Your Page Title">
<meta property="og:description" content="Compelling description for social sharing.">
<meta property="og:url" content="https://example.com/page-url/">
<meta property="og:type" content="website"><!-- or "article" -->
<meta property="og:locale" content="en_US">
<meta property="og:site_name" content="Your Site Name">


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


