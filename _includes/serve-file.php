<?php

// Function for fetching and serving file for download

// (Defining constants before function starts)
define ("SERVE_SUCCESS",          200);
define ("SERVE_ERROR_REQUEST",    400); // Not in use
define ("SERVE_ERROR_NOACCESS",   403);
define ("SERVE_ERROR_NOFILE",     404);

function serveFile(string $file): int {

    // Blocking calls to files/directories starting with _ or .
    foreach (explode('/', $file) as $part) {
        if (str_starts_with($part, '_') || str_starts_with($part, '.')) {
            return SERVE_ERROR_NOACCESS;
        }
    }

    $file = findIncludeFile($file);

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

    return SERVE_SUCCESS;

    // TO DO: IMPLEMENT GOAT COUNTER STATISTICS FOR FILE DOWNLOADS

}


?>