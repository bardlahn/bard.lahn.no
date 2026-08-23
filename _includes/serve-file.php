<?php

// Function for fetching and serving file for download

// (Defining constants before function starts)
define ("SERVE_SUCCESS",          200);
define ("SERVE_ERROR_REQUEST",    400); // Not in use
define ("SERVE_ERROR_NOACCESS",   403); // Not implemented (needs hook in findIncludeFile)
define ("SERVE_ERROR_NOFILE",     404);

function serveFile(string $filepath): int {

    $file = findIncludeFile($filepath);

    // If file does not exist, returning error
    if (!$file) {
        return SERVE_ERROR_NOFILE;
    }

    // Serving the file

    $mime = mime_content_type($file) ?: 'application/octet-stream';

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($file));
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');

    readfile($file);

    statCountPath($SERVER['REQUEST_URI']);
    logEvent("File served successfully: " . $filepath, LOG_INFO);

    return SERVE_SUCCESS;

}


?>