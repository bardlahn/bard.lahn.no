<div class="container">
    <div class="header">
        <div class="menu-icon" id="menuToggle">☰</div>
        <div class="site-name"><a href="/<?= $lang ?>">bard.lahn.no</a></div>
    </div>
    <div class="header-right">
        <div class="lang-toggle">
            <a href="/no/<?= $self_url ?>">no</a> / <a href="/en/<?= $self_url ?>">en</a>
        </div>
    </div>

    <?php 
    
    include $includes_path . 'md-render.php';
    echo '<div class="content"><h1>' . $self_title . '</h1></div>';

    if ($self_type == PAGE_SUB_BLOG) {
        $date = (new DateTime())->setTimestamp((int)$content['frontmatter']['date']);
        echo '<div class="content"><p>' . $date->format('d.m.Y') . '</p></div>';
    }

    renderMDContent($content['content']); 
    
    if ($self_type == PAGE_SUB_BLOG) {
        $tagtext = ($lang == "no") ? "Merket med" : "Tagged with";
        echo '<div class="content"><p>' . $tagtext . ': ';
        foreach($content['frontmatter']['tags'] as $tag) {
            $taglink = "/" . $lang . "/" . $self_path . "?tag=" . urlencode($tag);
            echo '<a href="' . $taglink . '">' . $tag . '</a> / ';    
        }
        echo '</p></div>';
    }

    ?>
