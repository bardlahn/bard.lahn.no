<?php

// Function for fetching and serving file for download

// (Defining constants before function starts)
define ("SERVE_SUCCESS",          200);
define ("SERVE_ERROR_REQUEST",    400);
define ("SERVE_ERROR_NOACCESS",   403);
define ("SERVE_ERROR_NOFILE",     404);

function serveFile(string $path, string $file): int {

    switch ($path ?? '') {
        case 'assets':
            global $assets_path;
            $base_path = $assets_path;
            break;
        case 'parent':
            global $self_path;
            $base_path = $self_path;
            break;
        case 'this':
            global $md_path;
            $base_path = $md_path;
            break;
        default:
            return SERVE_ERROR_REQUEST;
    }

    // Blocking calls to files/directories starting with _ or .
    foreach (explode('/', $file) as $part) {
        if (str_starts_with($part, '_') || str_starts_with($part, '.')) {
            return SERVE_ERROR_NOACCESS;
        }
    }

    // Resolve and validate the full path
    $filename = basename($file);
    $real_base = realpath($base_path);
    $full_path = realpath($real_base . '/' . $file);

    if (!$real_base || !$full_path || !str_starts_with($full_path, $real_base)) {
        return SERVE_ERROR_NOFILE;
    }

    if (!is_file($full_path)) {
        return SERVE_ERROR_NOFILE;
    }

    // Serving the file and returning success

    $mime = mime_content_type($full_path) ?: 'application/octet-stream';

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($full_path));
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    readfile($full_path);

    return SERVE_SUCCESS;

}


?>