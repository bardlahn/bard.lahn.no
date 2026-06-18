<!-- DEBUG

<?php
global $self_path_segments;
var_dump($self_path_segments);
?>

-->

<div class="container">
    <div class="header">
        <div class="menu-icon" id="menuToggle"><img src="<?= $assets_rel_path ?>icons/w95prog.32.png" height="32" width="32" alt="☰"></div>
        <div class="site-name"><a href="/<?= $lang ?>"><?= $site_title ?></a></div>
    </div>
    <div class="header-right">
        <div class="lang-toggle">
            <a href="/no/<?= $self_url ?>">no</a> / <a href="/en/<?= $self_url ?>">en</a>
        </div>
    </div>

    <?php 
    
    include_once $includes_path . 'md-render.php';
    echo '<div class="content"><h1>' . $self_title . '</h1></div>';

    if ($self_type == PAGE_SUB_BLOG) {
        $dt = $fmatter['date'] instanceof DateTime
            ? $fmatter['date']
            : (is_numeric($fmatter['date'])
                ? (new DateTime())->setTimestamp((int)$fmatter['date'])
                : new DateTime((string)$fmatter['date']));
        echo '<div class="content"><p>' . $dt->format('d.m.Y') . '</p></div>';
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
