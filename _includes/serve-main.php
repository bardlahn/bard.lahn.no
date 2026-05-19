<?php

// Before serving: Checks for any ACTION request

if (!empty($_GET['action'])) {
    switch (strtolower($_GET['action'])) {

        case 'download':
            include($includes_path."serve-file.php");
            $serve = serveFile($_GET['path'] ?? '', $_GET['file'] ?? '');
            if ($serve == SERVE_SUCCESS) {
                // Success serving file - exiting
                exit;
            } else {
                // Error - passing on error code and serving error page
                $serve_error = strval($serve);
                include($includes_path.'fetch_error.php');
            }
            break;

        // Other ACTION cases to be added as needed...

    }
}

// Proceeding to serve

// Pushes HTTP response code if set in frontmatter
if (!empty($fmatter['http-code'])) http_response_code($fmatter['http-code']);

// Entering main logic for serving HTML...

?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>

<?php

// Printing head elements

include($includes_path."html-head.php");
include($includes_path."css-styles.php");
include($includes_path."html-meta.php");
include($includes_path."scripts-head.php");

?>

</head>
<body>

<?php

// Printing body elements

include($includes_path."html-nav.php");
include($includes_path."html-body.php");
include($includes_path."html-footer.php");
include($includes_path."scripts-body.php");

?>

</body>
</html>