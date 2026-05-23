<?php

// Building and printing meta tags for HTML HEAD section. Currently handles
//   - General HTML metatags
//   - Schema.org metatags
//   - OpenGraph metatags
//   - Highwire Press metatags (for Google Scholar, only used for publications)
//   - Dublin Core basic metatags

$pre = "\n    ";


// Checking what type of page is displayed to print relevant meta data

if ($self_type != PAGE_ERROR) {

    // For all pages except error pages: Printing page description and canonical URL

    $meta_desc = $fmatter['abstract'] ?? $self_title . ' / ' . $site_title;
    $meta_desc = $fmatter['description'] ?? $meta_desc;
    echo $pre . "<meta name=\"description\" content=\"" . $meta_desc . "\">";

    $meta_canonical = $fmatter['routes']['canonical'] ?? ("/" . $self_url . "/");
    echo $pre . "<link rel=\"canonical\" href=\"" . $base_url . $meta_canonical . "\">";


} else {
    // If error page, printing nofollow
    echo $pre . '<meta name="robots" content="noindex, nofollow">';
}

// For all pages: Printing alternate language paths

foreach ($foundfiles as $lang_key => $file) {
    echo $pre . '<link rel="alternate" hreflang="'
     . htmlspecialchars($lang_key) . '" href="'
     . $base_url . '/' . htmlspecialchars($lang_key)
     . '/' .  $self_url . '">';
}

// Beginning OpenGraph and Schema.org output

$schemaJson['@context']  = 'https://schema.org';

if ($self_type != PAGE_ERROR) {

    // For all pages: Adding general metadata for...

    // ...Dublin Core
    echo $pre . '<link rel="schema.DC" href="https://purl.org/DC/elements/1.0/">';
    echo $pre . '<meta name="dc.Title" content="'.$self_title.'">';
    echo $pre . '<meta name="dc.Language" content="'.$lang.'">';

    // ...OpenGraph
    echo $pre . '<meta property="og:description" content="' . $meta_desc . '">';
    echo $pre . '<meta property="og:url" content="' . $base_url . '/' . $lang . $meta_canonical . '">';
    echo $pre . '<meta property="og:locale" content="' . $lang . '">';
    echo $pre . '<meta property="og:site_name" content="'.$site_title.'">';

    if (isset($fmatter['date'])) {
        $dt = (new DateTime('now', new DateTimeZone('Europe/Oslo')))
            ->setTimestamp($fmatter['date']);
        $meta_date = htmlspecialchars($dt->format('Y-m-d'));
    } else {
        $meta_date = "";
    }

    $meta_authors = getAuthors($fmatter['authors'] ?? 'self');

    if ($self_type == PAGE_MAIN) {

        if ($self_url == '') {

            // Root page: Returning website metadata

            echo $pre . '<meta property="og:type" content="website">';
            echo $pre . '<meta property="og:title" content="' . $self_title . ' / ' . $site_title . '">';

            // Adding Schema.org website properties
            $schemaJson['@type']         = 'WebSite';

        } elseif ($self_url == trim($self_profile_rel_path, '/')) {

            // Profile page: Returning profile metadata

            echo $pre . '<meta property="og:type" content="profile">';
            echo $pre . '<meta property="og:title" content="' . $meta_authors['self']['name'] . '">';
            if (!empty($meta_authors['self']['familyName']))    echo $pre . '<meta property="profile:first_name" content="'.$meta_authors['self']['familyName'].'">';
            if (!empty($meta_authors['self']['givenName']))     echo $pre . '<meta property="profile:last_name"  content="'.$meta_authors['self']['givenName'].'">';

            // Adding Schema.org person properties
            $schemaJson['@type']        = 'Person';
            $schemaJson                 = array_merge($schemaJson, $meta_authors['self']);

        } else {
            
            // General mainpage: Returning page metadata

            echo $pre . '<meta property="og:type" content="website">';
            echo $pre . '<meta property="og:title" content="' . $self_title . ' / ' . $site_title . '">';

            // Adding Schema.org webpage properties
            $schemaJson['@type']         = 'WebPage';

        }
        
    } elseif ($self_type == PAGE_SUB_BLOG) {

        // Printing OpenGraph article properties

        echo $pre . '<meta property="og:type" content="article">';
        echo $pre . '<meta property="og:title" content="' . $self_title . '">';
        echo $pre . '<meta property="article:published_time" content="' . $meta_date . '">';

        // Adding Schema.org article properties

        $schemaJson['@type']            = 'Article';
        $schemaJson['datePublished']    = $meta_date;

        // Printing author(s)
        foreach ($meta_authors as $author) {
            if (!empty($author['url'])) {
                echo $pre . '<meta property="article:author" content="' . htmlspecialchars($author['url']) . '">' . "\n";
                $schemaJson['author'][] = [
                    '@type' => 'Person',
                    'name'  => $author['name'],
                    'url'   => $author['url']
                    ];
            }
        }

    } elseif ($self_type == PAGE_SUB_PUB) {

        // Publication page type is handled as article in OpenGraph data...
        // ...unless pubtype is book, which is a separate og:type

        $og_pubtype = "article";
        $meta_publisher = $fmatter['pub-data']['publisher'] ?? '';
        $schema_publisher = ['@type' => 'Organization', 'name' => $meta_publisher];

        if (isset($fmatter['pub-data']['pubtype'])) {
            if (strtolower($fmatter['pub-data']['pubtype']) == 'book') {

                $og_pubtype = "book";

                // Printing OpenGraph and Highwire book properties
                echo $pre . '<meta property="og:type" content="book">';
                echo $pre . '<meta property="book:release_date" content="' . $meta_date .'">';
                echo $pre . '<meta property="citation_publisher" content="' . $meta_publisher .'">';

                if (!empty($fmatter['pub-data']['isbn'])) {
                    echo $pre . '<meta property="book:isbn" content="' . $fmatter['pub-data']['isbn'] .'">';
                    echo $pre . '<meta name="dc.Identifier" scheme="isbn" content="'.$fmatter['pub-data']['isbn'].'">';
                    echo $pre . '<meta name="citation_isbn" content="' . $fmatter['pub-data']['isbn'] . '">';
                }
                
            echo $pre . '<meta name="citation_title" content="'.$self_title.'">';

                // Adding Schema.org book properties
                $schemaJson['@type']         = 'Book';
                $schemaJson['isbn']          = $fmatter['pub-data']['isbn'] ?? '';
                $schemaJson['publisher']     = [$schema_publisher];

            } else {
                
                // Printing OpenGraph article properties
                echo $pre . '<meta property="og:type" content="article">';
                echo $pre . '<meta property="article:published_time" content="' . $meta_date . '">';
                $og_pubtype = "article";

                // Printing Highwire tags and adding Schema.org publication properties...

                if (strtolower($fmatter['pub-data']['pubtype']) == 'article') {
                
                    // ... for SCHOLARLY ARTICLES

                    $schemaJson['@type']        = 'ScholarlyArticle';
                    $schemaJson['pagination']   = $fmatter['pub-data']['pages'] ?? '';
                    $schemaJson['isPartOf']     = [[
                            '@type'            => 'PublicationIssue',
                            'issueNumber'      => $fmatter['pub-data']['issue'] ?? '',
                            'isPartOf'         => [[
                                '@type'        => 'PublicationVolume',
                                'volumeNumber' => $fmatter['pub-data']['volume'] ?? '',
                                'isPartOf'     => [[
                                    '@type'    => 'Periodical',
                                    'name'     => $fmatter['pub-data']['journal'] ?? '',
                                    'issn'     => $fmatter['pub-data']['issn'] ?? '',
                                    'publisher'=> [$schema_publisher]
                                    ]]
                                ]]
                            ]];

                    if (!empty($fmatter['pub-data']['journal']))
                        echo $pre . '<meta name="citation_journal_title" content="'.$fmatter['pub-data']['journal'].'">';
                    if (!empty($fmatter['pub-data']['volume']))
                        echo $pre . '<meta name="citation_volume" content="'.$fmatter['pub-data']['volume'].'">';
                    if (!empty($fmatter['pub-data']['issue']))
                        echo $pre . '<meta name="citation_issue" content="'.$fmatter['pub-data']['issue'].'">';

                } elseif (strtolower($fmatter['pub-data']['pubtype']) == 'report') {

                    // ... for REPORTS

                    $schemaJson['@type']        = 'Report';
                    $schemaJson['reportNumber'] = $fmatter['pub-data']['nubmer'] ?? '';
                    $schemaJson['publisher']    = [$schema_publisher];

                    if (!empty($fmatter['pub-data']['publisher']))
                        echo $pre . '<meta name="citation_technical_report_institution" content="'.$fmatter['pub-data']['publisher'].'">';
                    if (!empty($fmatter['pub-data']['number']))
                        echo $pre . '<meta name="citation_technical_report_number" content="'.$fmatter['pub-data']['nubmer'].'">';

                } elseif (strtolower($fmatter['pub-data']['pubtype']) == 'thesis') {
                
                    // ...for THESES

                    $schemaJson['@type']        = 'Thesis';
                    $schemaJson['inSupportOf']  = $fmatter['pub-data']['degree'] ?? '';
                    $schemaJson['publisher']    = [$schema_publisher];

                    if (!empty($fmatter['pub-data']['publisher']))
                        echo $pre . '<meta name="citation_dissertation_institution" content="'.$fmatter['pub-data']['publisher'].'">';

                } elseif (strtolower($fmatter['pub-data']['pubtype']) == 'chapter') {
                
                    // ...for BOOK CHAPTERS
                    
                    if (!empty($fmatter['pub-data']['isbn'])) {
                        echo $pre . '<meta name="dc.Identifier" scheme="isbn" content="'.$fmatter['pub-data']['isbn'].'">';
                        echo $pre . '<meta name="citation_isbn" content="' . $fmatter['pub-data']['isbn'] . '">';
                    }
          
                    $schemaJson['@type']        = 'Chapter';
                    $schemaJson['pagination']   = $fmatter['pub-data']['pages'] ?? '';
                    $schemaJson['isPartOf']     = [[
                            '@type'             => 'Book',
                            'name'              => $fmatter['pub-data']['book'] ?? '',
                            'publisher'         => [$schema_publisher]
                            ]];

                    // Adding editors if given
                    $editors = getAuthors($fmatter['pub-data']['editors'] ?? null);
                    if ($editors) {
                        foreach ($editors as $editor) {
                            $schemaJson['isPartOf']['editor'][] = [
                                '@type'         => 'Person',
                                'name'          => $editor[name] ?? ''
                                ];
                        }
                    }

                }

            }

        }

        // Printing OpenGraph, Highwire and Schema.org properties for all publications

        if (empty($schemaJson['@type'])) $schemaJson['@type'] = 'Article';
        $schemaJson['datePublished'] = $meta_date;
        
        if (!empty($fmatter['pub-data']['file']))
            echo $pre . '<meta name="citation_pdf_url" content="'.$base_url.'/'.$lang.'/'.$self_url.'?action=download&file='.$fmatter['pub-data']['file'].'">';

        echo $pre . '<meta name="citation_publication_date" content="'.$meta_date.'">';
        echo $pre . '<meta name="citation_title" content="'.$self_title.'">';
        echo $pre . '<meta property="og:title" content="' . $self_title . '">';

        // ...DOI info
        if (!empty($fmatter['pub-data']['doi'])) {
            $schemaJson['sameAs']   = 'https://dx.doi.org/' . $fmatter['pub-data']['doi'];
            echo $pre . '<meta name="dc.Identifier" scheme="doi" content="'.$fmatter['pub-data']['doi'].'">';
            echo $pre . '<meta name="citation_doi" content="'.$fmatter['pub-data']['doi'].'">';
        }

        if (!empty($fmatter['pub-data']['issn'])) {
            echo $pre . '<meta name="dc.Identifier" scheme="issn" content="'.$fmatter['pub-data']['issn'].'">';
            echo $pre . '<meta name="citation_issn" content="'.$fmatter['pub-data']['issn'].'">';
        }

        // ...author info
        foreach ($meta_authors as $author) {

            $schemaJson['author'][] = [
                '@type' => 'Person',
                'name'  => $author['name'] ?? '',
                'url'   => $author['url'] ?? ''
                ];

            if (!empty($author['orcid'])) {
                $author['url'] = $author['url'] ?? 'https://orcid.org/' . $author['orcid'];
                echo $pre . '<meta name="citation_author_orcid" content="https://orcid.org/'.$author['orcid'].'">';
            }

            if (!empty($author['url'])) {
                echo $pre . '<meta property="' . $og_pubtype . ':author" content="' . htmlspecialchars($author['url']) . '">' . "\n";
            } 
            
            if (!empty($author['name']))
                echo $pre . '<meta name="citation_author" content="'.htmlspecialchars($author['name']).'">';
                echo $pre . '<meta name="dc.Creator" content="'.$author['name'].'">';

        }

    }

    if (isset($schemaJson['@type'])) {

        // Schema.org type is set - proceeding to printing JSON-LD script

        $schemaJson['name'] = $self_title;
        $schemaJson['url'] = $schemaJson['url'] ?? $base_url . '/' . $lang . $meta_canonical;
        $schemaJson['inLanguage'] = $lang;
        $schemaJson['abstract'] = $meta_desc;

        $jsonLD = json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n\n<script type=\"application/ld+json\">\n" . $jsonLD . "\n</script>\n";

    }

}

?>


