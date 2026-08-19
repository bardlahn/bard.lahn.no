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

    // IN PROGRESS: Counting download for statistics

    $service = getConfig('stats-secret', '', 'service');

    if ($service) {

        $url = $service['url'] . $service['endpoint'] ?? '';
        $token = $service['token'] ?? '';

        if ($url && $token) {
            
            $data = [
                'no_sessions' => true,
                'hits'        => [['path' => '/?action=download&file=' . $filepath]],
            ];

            $payload = json_encode($data);
            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token,
                ],
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_RETURNTRANSFER => true,
            ]);

            $response = curl_exec($ch);

            if ($response === false) {
                $error = curl_error($ch);
                curl_close($ch);
                die('Curl error: ' . $error);
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            echo "HTTP Status: " . $httpCode . "\n";
            echo "Response: " . $response . "\n";
            die();

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