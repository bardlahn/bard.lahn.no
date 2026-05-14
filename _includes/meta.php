<?php

$description = $content['frontmatter']['description'] ?? "Personal website of Bård Lahn: " . $self_title;
echo "<meta name=\"description\" content=\"" . $description . "\">";

$canonical =  $self_url . "/";
echo "<link rel=\"canonical\" href=\"" . $canonical . "\">";


// Code to include meta tags for SEO and socials sharing

/*

Languages:

<link rel="alternate" hreflang="en" href="https://example.com/page/">
<link rel="alternate" hreflang="fr" href="https://example.com/fr/page/">
<link rel="alternate" hreflang="x-default" href="https://example.com/page/">


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