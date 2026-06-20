<?php include_once $includes_path . 'md-render.php'; ?>

<div class="container">
    <div class="header">
        <div class="menu-icon" id="menuToggle"><img src="<?= $assets_rel_path ?>icons/w95prog.32.png" height="32" width="32" alt="☰"></div>
        <div class="site-name">
            <a href="/<?= $lang ?>"><?= $site_title ?></a>
<?php

$breadcrumbs = (count($self_url_segments) > 1) 
    ? buildBreadcrumbs(array_slice($self_url_segments, 0, -1), $lang)
    : null;

if ($breadcrumbs) {
    foreach ($breadcrumbs as $crumb) {
        echo '          / <a href="' . $crumb['url'] . '">' . $crumb['title'] . "</a>\n";
    }
}

?>
        /
        </div>
    </div>
    <div class="header-right">
        <div class="lang-toggle">
<?php

// Looping through available languages to print language switcher links
// (using custom paths if defined in front matter)

$i = 0;

foreach ($lang_list as $langCode => $langName) {

    $langPath = '';

    if (isset($fmatter['routes']['languages'][$langCode])) {
        $langPath = ltrim($fmatter['routes']['languages'][$langCode], '/');
        $langPath = (strpos($langPath, $langCode.'/') === 0) 
                    ? substr($langPath, strlen($langCode)+1)
                    : $langPath;
    }

    $langPath = (empty($langPath)) ? '/'.strtolower($langCode).'/'.$self_url : $langPath;

    $i++;
    $delim = ($i < count($lang_list)) ? ' / ' : '';
    echo '            <a href="'. $langPath . '">' . $langCode . '</a>'. $delim . "\n";

}

?>
        </div>
    </div>

    <div class="content"><h1><?=$self_title ?></h1></div>

<?php

if ($self_type == PAGE_SUB_BLOG) {
    $postDate = $fmatter['date'] instanceof DateTime
        ? $fmatter['date']
        : (is_numeric($fmatter['date'])
            ? (new DateTime())->setTimestamp((int)$fmatter['date'])
            : new DateTime((string)$fmatter['date']));
    echo '<div class="content"><p>' . $postDate->format('d.m.Y') . "</p></div>\n";
}

renderMDContent($content); 

if ($self_type == PAGE_SUB_BLOG) {
    $tagtext = ($lang == "no") ? "Merket med" : "Tagged with";
    echo '<div class="content"><p>' . $tagtext . ': ';
    foreach($fmatter['tags'] as $tag) {
        $taglink = "/" . $lang . "/" . $self_path . "?tag=" . urlencode($tag);
        echo '<a href="' . $taglink . '">' . $tag . '</a> / ';    
    }
    echo '</p></div>';
}

?>
