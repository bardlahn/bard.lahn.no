<?php

/*

Bård Lahn / bard.lahn.no - personal website based on PHP and Markdown
---------------------------------------------------------------------

This is the main logic of the site, which serves all dynamic content.
This file (index.php) and the file _paths.php must be present in the root dir.

*/

// Setting up initial path config
include '_paths.php';

// Running initialisation
include $includes_path . "init.php";

// Fetching content
include $includes_path . "fetch-main.php";

// Serving content
include $includes_path . "serve-main.php";

?>