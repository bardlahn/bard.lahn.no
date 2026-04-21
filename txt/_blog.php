<?php

global $self_url;
global $includes_path;
global $lang;

include $includes_path . 'fetch-sub.php';

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

$blog = fetchSubEntries($root_path . $self_url, $filter);

$total_posts = count($blog['sub_items']);
$show_all    = isset($_GET['NumberPosts']) && (int)$_GET['NumberPosts'] === 0;

$start_from  = isset($_GET['StartFrom'])   ? max(0, (int)$_GET['StartFrom'])  : 0;
$num_posts   = isset($_GET['NumberPosts'])  ? max(0, (int)$_GET['NumberPosts']) : 10;

// Apply slice, or use full array if showing all
$posts_to_show = $show_all ? $blog['sub_items'] : array_slice($blog['sub_items'], $start_from, $num_posts);

// How many are actually being shown
$showing_count = count($posts_to_show);
$showing_from  = $total_posts > 0 ? $start_from + 1 : 0;
$showing_to    = $start_from + $showing_count;

$txt_show      = ($lang == "no") ? "Viser poster"   : "Showing posts";
$txt_of        = ($lang == "no") ? "av"             : "of";
$txt_prev      = ($lang == "no") ? "Forrige"        : "Previous";
$txt_next      = ($lang == "no") ? "Neste"          : "Next";
$txt_all       = ($lang == "no") ? "Vis alle"       : "Show all";
$txt_less      = ($lang == "no") ? "Vis mindre"     : "Show less";

// If filter applies, showing information about filter and total posts

if (!empty($filter_descriptions)) {

    if ($lang == "no") {
        $summary = "Totalt <strong>{$total_posts}</strong> poster er merket med " . implode(" og ", $filter_descriptions) . ".";
    } else {
        $summary = "A total of <strong>{$total_posts}</strong> posts matching " . implode(" and ", $filter_descriptions) . ".";
    }

    echo "<p>{$summary}</p>\n<p>{$txt_show} <strong>{$showing_from}–{$showing_to}</strong>.</p>\n";

}

foreach ($posts_to_show as $entry) {
    echo "<p><h3><a href=\"/" . $lang . "/" . $self_url . "/" . $entry['slug'] . "\">" . $entry['title'] . "</a></h3>\n";
    $timestamp = $entry['date'] instanceof DateTime ? $entry['date']->getTimestamp() : (int)$entry['date'];
    $date = (new DateTime())->setTimestamp((int)$timestamp);
    echo "(" . $date->format('m.d.Y') . ")\n";
    echo "<br/>" . $entry['abstract'] . "</p>\n\n";
}

echo "<p>{$txt_show} {$showing_from}–{$showing_to} {$txt_of} {$total_posts}</p>\n";

if (!$show_all) {
    if ($start_from > 0) {
        $prev_start = max(0, $start_from - $num_posts);
        echo "<a href=\"?{$filter_query}&StartFrom={$prev_start}&NumberPosts={$num_posts}\">← {$txt_prev}</a> ";
    }

    if ($showing_to < $total_posts) {
        $next_start = $start_from + $num_posts;
        echo "<a href=\"?{$filter_query}&StartFrom={$next_start}&NumberPosts={$num_posts}\">{$txt_next} →</a> ";
    }

    echo "<a href=\"?{$filter_query}&StartFrom=0&NumberPosts=0\">{$txt_all}</a>";
} else {
    echo "<a href=\"?{$filter_query}\">{$txt_less}</a>";
}

echo "</div>\n\n";

?>