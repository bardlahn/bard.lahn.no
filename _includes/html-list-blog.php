<?php

global $self_url;
global $includes_path;
global $lang;
global $site_config;

include_once $includes_path . 'fetch-sub.php';

echo "<div class=\"content\">\n\n";

// Fetching language strings
$txt = getConfig('strings', $lang, 'list-blog');

// Handling filtering based on query

$allowedFilters = ['year', 'tag', 'lang'];
$filters[] = "lang=" . $lang; // Setting default language
$filterDesc = [];
$langDesc = "";

foreach ($allowedFilters as $key) {
    if (isset($_GET[$key])) {
        $value = htmlspecialchars(strip_tags($_GET[$key]));
        if ($key == 'lang') {
            $langName = $site_config['languages'][$value]['name'] ?? $txt['lang'] . " '" . $value . "'";
            $langDesc = $txt['show'] . " " . $txt['in'] . " " . $langName;
            echo "<!-- DEBUG: langDesc {$langDesc} -->";
        } else {
            $filterDesc[] = $key . ' <strong>' . $value . '</strong>';
        }
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

$blog = fetchSubEntries($root_path . $self_url, $filter, $sorting);

$total_posts = count($blog['sub-items']);
$show_all    = isset($_GET['NumberPosts']) && (int)$_GET['NumberPosts'] === 0;

$start_from  = isset($_GET['StartFrom'])   ? max(0, (int)$_GET['StartFrom'])  : 0;
$num_posts   = isset($_GET['NumberPosts'])  ? max(0, (int)$_GET['NumberPosts']) : 10;

// Apply slice, or use full array if showing all
$posts_to_show = $show_all ? $blog['sub-items'] : array_slice($blog['sub-items'], $start_from, $num_posts);

// How many are actually being shown
$showing_count = count($posts_to_show);
$showing_from  = $total_posts > 0 ? $start_from + 1 : 0;
$showing_to    = $start_from + $showing_count;

// If filter applies, showing information about filter and total posts

if (!empty($filterDesc)) {
    $summary = $txt['total']."<strong>{$total_posts}</strong>".$txt['marked'].implode($txt['and'], $filterDesc) . ".";
    echo "<p>{$summary}</p>\n<p>".$txt['show']." <strong>{$showing_from}–{$showing_to}</strong>.</p>\n";
    echo (!empty($langDesc)) ? "<p>{$langDesc}.</p>\n" : "";
}

foreach ($posts_to_show as $entry) {
    echo "<p><h2><a href=\"/" . $lang . "/" . $self_url . "/" . $entry['slug'] . "\">" . $entry['title'] . "</a></h2>\n";
    // $timestamp = $entry['date'] instanceof DateTime ? $entry['date']->getTimestamp() : (int)$entry['date'];
    // $date = (new DateTime())->setTimestamp((int)$timestamp);
    $date = (new DateTime($entry['date']))->format('d.m.Y');
    echo "(" . $date . ")\n";
    echo "<br/>" . $entry['abstract'] . "</p>\n\n";
}

echo "<p>".$txt['show']." {$showing_from}–{$showing_to} ".$txt['of']." {$total_posts}</p>\n";

if (!$show_all) {
    if ($start_from > 0) {
        $prev_start = max(0, $start_from - $num_posts);
        echo "<a href=\"?{$filter_query}&StartFrom={$prev_start}&NumberPosts={$num_posts}\">← ".$txt['prev']."</a> ";
    }

    if ($showing_to < $total_posts) {
        $next_start = $start_from + $num_posts;
        echo "<a href=\"?{$filter_query}&StartFrom={$next_start}&NumberPosts={$num_posts}\">".$txt['next']." →</a> ";
    }

    echo "<a href=\"?{$filter_query}&StartFrom=0&NumberPosts=0\">".$txt['all']."</a>";
} else {
    echo "<a href=\"?{$filter_query}\">".$txt['less']."</a>";
}

echo "</div>\n\n";

?>