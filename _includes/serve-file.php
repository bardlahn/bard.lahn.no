<?php

// Function for fetching and serving file for download

function serveFile(string $filepath): int {

    try {
        $file = findIncludeFile($filepath);
    } catch (ServeException $e) {
        return $e->getStatus();
    }

    // If file does not exist, returning error
    if (!$file) {
        return SERVE_ERROR_NOFILE;
    }

    // Before returning file: Logging and counting hit
    statCountPath($_SERVER['REQUEST_URI']);
    logEvent("File served successfully: " . $filepath, LOG_INFO);

    // Serving the file

    $mime = mime_content_type($file) ?: 'application/octet-stream';

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($file));
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');

    readfile($file);

    return SERVE_SUCCESS;

}


?>