<?php

// Before serving: Checks for any ACTION request

if (!empty($_GET['action'])) {
    switch (strtolower($_GET['action'])) {

        case 'download':
            include_once($includes_path."serve-file.php");
            $serve = serveFile($_GET['file'] ?? '');
            if ($serve == SERVE_SUCCESS) {
                // Success serving file - exiting
                statCountPath($SERVER['REQUEST_URI']);
                echo logEvent("File served successfully: " . $_GET['file'], LOG_INFO);
                exit;
            } else {
                // Error - passing on error code and serving error page
                $serve_error = strval($serve);
                include($includes_path.'fetch-error.php');
            }
            break;
    
        case 'cite':
            if ($self_type == PAGE_SUB_PUB) {
                include_once($includes_path."serve-citation.php");
                $cite = serveCitation($fmatter);
                if ($cite == SERVE_SUCCESS) {
                    // Success serving citation file - exiting
                    statCountPath($SERVER['REQUEST_URI']);
                    logEvent("Citation served successfully for publication: " . 
                        ($fmatter['title'] ?? 'n/a'), LOG_INFO);
                    exit;
                } else {
                    $serve_error = strval($cite);
                }
            } else {
                $serve_error = 400;
            }
            include($includes_path.'fetch-error.php');
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

if (!empty($fmatter['include-head'])) {
    // If 'include-head' is set in frontmatter, include the specified file(s)
    if (is_array($fmatter['include-head'])) {
        foreach ($fmatter['include-head'] as $file) {
            $headInclude = findIncludeFile($file);
            if ($headInclude) {
                include($headInclude);
            } else {
                echo logEventHTML("Specified inclusion file not found - "
                 . htmlspecialchars($file), LOG_WARNING);
            }
        }
    } else {
        $headInclude = findIncludeFile($fmatter['include-head']);
        if ($headInclude) {
            include($headInclude);
        } else {
                echo logEventHTML("Specified inclusion file not found - "
                 . htmlspecialchars($file), LOG_WARNING);
        }
    }
}

?>

</head>
<body>

<?php

// Printing body elements

if (!empty($fmatter['include-top'])) {
    // If 'include-top' is set in frontmatter, include the specified file(s)
    if (is_array($fmatter['include-top'])) {
        foreach ($fmatter['include-top'] as $file) {
            $headInclude = findIncludeFile($file);
            if ($headInclude) {
                include($headInclude);
            } else {
                echo logEventHTML("Specified inclusion file not found - "
                 . htmlspecialchars($file), LOG_WARNING);
            }
        }
    } else {
        $headInclude = findIncludeFile($fmatter['include-top']);
        if ($headInclude) {
            include($headInclude);
        } else {
            echo logEventHTML("Specified inclusion file not found - "
             . htmlspecialchars($fmatter['include-top']), LOG_WARNING);
        }
    }
}

include($includes_path."html-nav.php");
include($includes_path."html-body.php");
include($includes_path."html-footer.php");

if (!empty($fmatter['include-bottom'])) {
    // If 'include-bottom' is set in frontmatter, include the specified file(s)
    if (is_array($fmatter['include-bottom'])) {
        foreach ($fmatter['include-bottom'] as $file) {
            $headInclude = findIncludeFile($file);
            if ($headInclude) {
                include($headInclude);
            } else {
                echo logEventHTML("Specified inclusion file not found - "
                . htmlspecialchars($file), LOG_WARNING);
            }
        }
    } else {
        $headInclude = findIncludeFile($fmatter['include-bottom']);
        if ($headInclude) {
            include($headInclude);
        } else {
            echo logEventHTML("Specified inclusion file not found - "
                . htmlspecialchars($fmatter['include-bottom']), LOG_WARNING);
        }
    }
}

include($includes_path."scripts-body.php");


// ...all done!

?>

</body>
</html>