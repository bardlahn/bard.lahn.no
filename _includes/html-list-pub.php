<?php

global $self_url;
global $includes_path;
global $lang;
global $site_config;

include_once $includes_path . 'fetch-sub.php';

echo "<div class=\"content\">\n\n";

// Handling filtering based on query

$allowedFilters = ['year', 'tag', 'pub-type', 'lang'];
$filters = [];
// $filters[] = "lang=" . $lang;  // - NO LANGUAGE DIFFERENTIATION
$filter_descriptions = [];

foreach ($allowedFilters as $key) {
    if (isset($_GET[$key])) {
        $value = htmlspecialchars(strip_tags($_GET[$key]));
        $filter_descriptions[] = $key . ' <strong>' . $value . '</strong>';
        $filters[] = $key . '=' . $value;
    }
}

$filter = implode(',', $filters);
$filter_query = implode('&', $filters);

$sorting = '';
if (!empty($_GET['SortBy'])) {
    $sortBy  = htmlspecialchars(strip_tags($_GET['SortBy']));
    $sortDir = htmlspecialchars(strip_tags($_GET['SortDir'])) ?? 'descending';
    $sorting = $sortBy . '=' . $sortDir;
}

$pub = fetchSubEntries($root_path . $self_url, $filter, $sorting);
$total_posts = count($pub['sub-items']);
$posts_to_show = $pub['sub-items'];

// Fetching language strings
$txt = getConfig('strings', $lang, 'list-pub');

// If filter applies, showing information about filter and total posts

if (!empty($filter_descriptions)) {

    if ($lang == "no") {
        $summary = "<p>Totalt <strong>{$total_posts}</strong> publikasjoner er merket med " . implode($txt['and'], $filter_descriptions) . ".</p>\n";
    } else {
        $summary = "<p>A total of <strong>{$total_posts}</strong> publications matching " . implode($txt['and'], $filter_descriptions) . ".</p>\n";
    }

}

foreach ($posts_to_show as $entry) {
    echo "<p>";

    $authors = getAuthors($entry['authors'] ?? 'self');
    if (!$authors) {
        // No authors returned - not even 'self'. NEEDS BETTER ERROR HANDLING!
        die('For some reason, no authors were returned');
    }

    $formatAuthor = function ($author): string {
        if (is_array($author)) {
            $name = htmlspecialchars($author['name'] ?? '(no name given)');
        } else {
            $name = $author ?? '(no name given)';
        }
        // $url  = $author['url'] ?? '';
        $url = ""; // No URL links in author names
        return $url
            ? '<a href="' . htmlspecialchars($url) . '">' . $name . '</a>'
            : $name;
    };

    $parts  = array_values(array_filter(array_map($formatAuthor, $authors)));
    $count = count($authors);

    if ($count > 3) {
        echo implode(', ', array_slice($parts, 0, 3)) . ', et al.';
    } elseif ($count === 1) {
        echo $parts[0];
    } elseif ($count === 2) {
        echo $parts[0] . $txt['and'] . $parts[1];
    } else {
        echo $parts[0] . ', ' . $parts[1] . $txt['and'] . $parts[2];
    }

    // Adding punctuation to title if not already included
    $title = preg_replace('/([^.!?,;:])\s*$/', '$1.', $entry['title']);
    $pubString = '';

    // Printing date and title
    $date = $entry['date'] instanceof DateTime
    ? $entry['date']
    : (is_numeric($entry['date']) && (int)$entry['date'] <= 9999
        ? new DateTime((int)$entry['date'] . '-01-01')
        : new DateTime((string)$entry['date']));
    echo " (" . $date->format('Y') . "). <strong>" . $title . "</strong> ";

    switch (strtolower($entry['pub-data']['pub-type'])) {

        case 'article':
            $pubString .= "<i>";
            $pubString .= $entry['pub-data']['journal'] ?? "<!-- DEBUG: Missing journal title -->";
            $pubString .= "</i> ";
            $pubString .= $entry['pub-data']['volume'] ?? "<!-- DEBUG: Missing volume -->";
            $pubString .= (!empty($entry['pub-data']['issue'])) ?
                " (" . $entry['pub-data']['issue'] . ")" : "<!-- DEBUG: Missing issue -->";
            $pubString .= (!empty($entry['pub-data']['pages'])) ?
                ": " . $entry['pub-data']['pages'] . ". " : "<!-- DEBUG: Missing pages -->";
            break;

        case 'chapter':
            $pubString .= (!empty($entry['pub-data']['pages'])) ?
                $txt['pages'] . " " . $entry['pub-data']['pages'] . " " . strtolower($txt['in']) . " <i>" :
                 "<!-- DEBUG: Missing issue -->" . $txt['in'] . " <i>";
            $pubString .= $entry['pub-data']['book'] ?? "<!-- DEBUG: Missing book title -->";
            $pubString .= "</i>";

            // Fetching and processing editor names
            $editors = getAuthors($entry['pub-data']['editors'] ?? '');
            if ($editors) {
                $edString = '';
                foreach ($editors as $editor) {
                    if (!empty($editor['name'])) {
                        $edString .= (empty($edString)) ? $editor['name'] : ", " . $editor['name'];
                    }
                }
                $pubString .= " (" . $txt['ed'] . " " . $edString . "). ";
            } else {
                $pubString .= ". <!-- DEBUG: Missing editors -->";
            }

            // No break - continuing to adding book details

        case 'book':
            $pubString .= (!empty($entry['pub-data']['place'])) ?
                $entry['pub-data']['place'] . ": " : "<!-- DEBUG: Missing place -->";
            $pubString .= (!empty($entry['pub-data']['publisher'])) ?
                $entry['pub-data']['publisher'] . "." : "<!-- DEBUG: Missing publisher -->";
            $pubString .= (!empty($entry['pub-data']['isbn'])) ?
                " (ISBN " . $entry['pub-data']['isbn'] . ")" : "<!-- DEBUG: Missing ISBN -->";
            break;
        
        case 'thesis':
            $pubString .= (!empty($entry['pub-data']['degree'])) ?
                $txt['deg'] . " (" . $entry['pub-data']['degree'] . "), " : "<!-- DEBUG: Missing degree -->";
            $pubString .= $entry['pub-data']['publisher'] ?? "<!-- DEBUG: Missing publisher -->";
            $pubString .= ".";
            break;

        default:
            // Other cases handled as report
            $pubString .= (!empty($entry['pub-data']['number'])) ?
                "(Report " . $entry['pub-data']['number'] . ") " : "";
            $pubString .= $entry['pub-data']['place'] ?? "<!-- DEBUG: Missing place -->";
            $pubString .= (!empty($entry['pub-data']['place']) && !empty($entry['pub-data']['publisher'])) ? ": " : "";
            $pubString .= $entry['pub-data']['publisher'] ?? "<!-- DEBUG: Missing publisher -->";
            $pubString .= ".";
            break;

    }

    echo $pubString . "\n<br>";

    // Printing links
    // TO DO: Check for publication MD content and link to it if present

    $pubString = "";
    if (!empty($entry['routes']['external'])) {
        $pubString .= '<a href="'.htmlspecialchars($entry['routes']['external']).'">'.$txt['goto']."</a>\n";
    }
    if (!empty($entry['pub-data']['file'])) {
        $pdfLink = "?action=download&file=" . $entry['pub-data']['file'];
        $pubString .= (empty($pubString)) ?
            '<a href="'.$pdfLink.'">'.$txt['pdf']."</a>\n" :
            ' / <a href="'.$pdfLink.'">'.$txt['pdf']."</a>\n";
    }

    $citeLink = "/" . $lang . "/" . $self_url . "/" . $entry['slug'] . "?action=cite&format=ris";
    $pubString .= (empty($pubString)) ?
        '<a href="'.$citeLink.'">'.$txt['cite']."</a>\n" :
        ' / <a href="'.$citeLink.'">'.$txt['cite']."</a>\n";

    echo $pubString . "</p>\n\n";

}

echo "</div>\n\n";

?>