<?php

// Function for fetching and serving file for download

// (Defining constants before function starts)
define ("SERVE_SUCCESS",          200);
define ("SERVE_ERROR_REQUEST",    400); // Not in use
define ("SERVE_ERROR_NOACCESS",   403); // Not implemented (needs hook in findIncludeFile)
define ("SERVE_ERROR_NOFILE",     404);

function serveFile(string $file): int {

    $file = findIncludeFile($file);

    // If file does not exist, returning error
    if (!$file) {
        return SERVE_ERROR_NOFILE;
    }

    // IN PROGRESS: Counting download for statistics

    $service = getConfig('stats.secret', $element = "service");

    if ($service) {
        $stats_url = $service['url'] ?? '';
        $stats_endpoint = $service['endpoint'] ?? '';
        $stats_key = $service['token'] ?? '';

        if ($stats_url && $stats_key && $stats_endpoint) {
            
            // $url = rtrim($stats_url, '/') . '/api/download-count.php?key=' . urlencode($stats_key) . '&file=' . urlencode($file_name);
            // @file_get_contents($url);
        }
    }

    // Serving the file

    $mime = mime_content_type($file) ?: 'application/octet-stream';

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($file));
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');

    readfile($file);

    return SERVE_SUCCESS;

}


?>