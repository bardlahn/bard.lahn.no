<?php

// Listing the languages in which the requested page is available

global $foundfiles;
global $lang_list;
global $self_url;

echo "\n<p><ul>\n";

if (is_array($foundfiles) && count($foundfiles) > 0) {
    foreach ($foundfiles as $lang_key => $file) {
        echo '<li><a href="/' . htmlspecialchars($lang_key) . '/' . $self_url . '">';
        echo $lang_list[$lang_key] ?? $lang_key;
        echo "</a></li>\n";
    }
} else {
    echo "<li>(No other languages available)</li>\n";
}

echo "</ul></p>\n\n";

?>