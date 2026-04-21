    <!-- Menu overlay -->
    <div class="menu-overlay" id="menuOverlay"></div>
    
    <!-- Navigation menu -->
    <nav class="nav-menu" id="navMenu">
        <button class="nav-menu-close" id="menuClose">&times;</button>
        <ul>
<?php

// Prints navigation menu based on language

if ($lang === 'no') {
    ?>
            <li><a href="/no/">     Hjem            </a></li>
            <li><a href="/no/bio/"> Om meg          </a></li>
            <li><a href="/no/txt/"> Tekster         </a></li>
            <li><a href="/no/div/"> Prosjekter      </a></li>
            <li><a href="/no/pub/"> Publikasjoner   </a></li>
    <?php
} else {
    ?>
            <li><a href="/en/">     Home            </a></li>
            <li><a href="/en/bio/"> About me        </a></li>
            <li><a href="/en/div/"> Projects        </a></li>
            <li><a href="/en/pub/"> Publications    </a></li>
    <?php
}
?>
        </ul>
    </nav>
