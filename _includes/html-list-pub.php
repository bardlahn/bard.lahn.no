<?php

global $self_url;
global $includes_path;
global $lang;

include_once $includes_path . 'fetch-sub.php';

echo "<div class=\"content\">\n\n";

// Handling filtering based on query

$allowedFilters = ['year', 'tag', 'category', 'lang'];
$filters = [];
$filters[] = "lang=" . $lang; // Setting default language
$filter_descriptions = [];

foreach ($allowedFilters as $key) {
    if (isset($_GET[$key])) {
        $value = htmlspecialchars(strip_tags($_GET[$key]));
        $filter_descriptions[] = $key . ' <strong>' . $value . '</strong>'; // TO DO: LANGUAGE DIFFERENTIATION
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

$txt_show      = ($lang == "no") ? "Viser poster"   : "Showing posts";
$txt_of        = ($lang == "no") ? "av"             : "of";
$txt_prev      = ($lang == "no") ? "Forrige"        : "Previous";
$txt_next      = ($lang == "no") ? "Neste"          : "Next";
$txt_all       = ($lang == "no") ? "Vis alle"       : "Show all";
$txt_less      = ($lang == "no") ? "Vis mindre"     : "Show less";

// If filter applies, showing information about filter and total posts

if (!empty($filter_descriptions)) {

    if ($lang == "no") {
        $summary = "<p>Totalt <strong>{$total_posts}</strong> publikasjoner er merket med " . implode(" og ", $filter_descriptions) . ".</p>\n";
    } else {
        $summary = "<p>A total of <strong>{$total_posts}</strong> publications matching " . implode(" and ", $filter_descriptions) . ".</p>\n";
    }

}

foreach ($posts_to_show as $entry) {
    echo "<p><h2><a href=\"/" . $lang . "/" . $self_url . "/" . $entry['slug'] . "\">" . $entry['title'] . "</a></h2>\n";
    $timestamp = $entry['date'] instanceof DateTime ? $entry['date']->getTimestamp() : (int)$entry['date'];
    $date = (new DateTime())->setTimestamp((int)$timestamp);
    echo "(" . $date->format('d.m.Y') . ")\n";
    echo "<br/>" . $entry['abstract'] . "</p>\n\n";

    // THIS IS WHERE FORMATTING OF PUBLICATIONS NEEDS TO HAPPEN



}

echo "</div>\n\n";

?>