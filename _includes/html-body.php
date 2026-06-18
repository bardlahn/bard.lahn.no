
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

    $breadcrumbs = (count($self_url_segments) > 1) 
        ? buildBreadcrumbs(array_slice($self_url_segments, 0, -1), $lang)
        : null;

    if ($self_type == PAGE_SUB_BLOG) {
        $postDate = $fmatter['date'] instanceof DateTime
            ? $fmatter['date']
            : (is_numeric($fmatter['date'])
                ? (new DateTime())->setTimestamp((int)$fmatter['date'])
                : new DateTime((string)$fmatter['date']));
    } else $postDate = null;

    if ($breadcrumbs OR $postDate) {
        echo "<div class=\"content\"><p>\n";
        echo ($postDate) ? $postDate->format('d.m.Y') : '';
        if ($breadcrumbs) {
            echo ($postDate) ? "<br>\n" : "";
            echo '<a href="/' . $lang . '/">' . $site_title . '</a> / ';
            foreach ($breadcrumbs as $crumb) {
                echo '<a href="' . $crumb['url'] . '">' . $crumb['title'] . '</a> / ';
            }
        }
        echo "\n</p></div>";
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
