<?php

// Function for fetching and serving file for download

function serveFile(string $filepath, bool $attachment = false): int {

    try {
        $file = findIncludeFile($filepath);
    } catch (ServeException $e) {
        return $e->getStatus();
    }

    // If file does not exist, returning error
    if (!$file) {
        return SERVE_ERROR_NOFILE;
    }

    // Setting MIME type
    $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
    ];

    if (isset($mimeTypes[$ext])) {
        $mime = $mimeTypes[$ext];
    } else {
        $mime = mime_content_type($file) ?: 'application/octet-stream';
    }

    // Before returning file: Logging and counting hit
    statCountPath($_SERVER['REQUEST_URI']);
    logEvent("File served successfully: " . $filepath . " (MIME=" . $mime . ")", LOG_INFO);

    // Serving the file

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($file));
    if ($attachment) {
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    }

    // Setting cache time: 30 days for images and PDFs, 1 day for other files
    $cacheTime = (str_starts_with($mime, 'image/') || $mime === 'application/pdf') ? "2592000" : "86400";
    header('Cache-Control: public, max-age='.$cacheTime.', immutable');
    // ALTERNATIVE FOR TESTING: header('Cache-Control: no-cache, no-store, must-revalidate');

    readfile($file);

    return SERVE_SUCCESS;

}


?>