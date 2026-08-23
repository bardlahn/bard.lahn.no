<?php

// Introducing public functions to fetch various configs

function getConfig(string $configfile, string $lang = '', string $element = '') {

    // Fetching config from frontmatter of specified file.
    // If $lang is set, checks for filename containing language suffix.
    // If $element is set, returns only the specified frontmatter (top-level) element.

    global $includes_path;
    global $config_path;

    $file = $config_path . $configfile . '.md';
    if (!empty($lang)) {
        // $lang is set - checking if language-specific file exists
        $check = $config_path . $configfile . '.' . $lang . '.md';
        $file = (file_exists($check)) ? $check : $file;
    }
    if (file_exists($file)) {
        // File exists - parses and returns
        $config = parseMDFile($file);
        if (!empty($element) && isset($config['frontmatter'][$element])) {
            // $element is set - returning only specified element
            return $config['frontmatter'][$element];
        } else {
            // Returning full frontmatter
            return $config['frontmatter'];
        }
    } else {
        // Config file does not exist
        return false;
    }

}

function getAuthors(mixed $raw): mixed {

    // Assembling author list from frontmatter, returning as an array

    // Fetching predefined authors from config file and processing them
    $config = getConfig('authors', element: 'authors');
    if (!$config) {
        return false;
    }
    $config = array_merge(...array_values($config));
    $authConfig = [];
    foreach ($config as $aK => $aC) {
        if (isset($aC['worksFor'])) {
            $aC['worksFor']['@type'] = "Organization";
        }
        if (isset($aC['orcid'])) {
            $aC['sameAs'] = $aC['sameAs'] ?? 'https://orcid.org/' . $aC['orcid'];
            $aC['url'] = $aC['url'] ?? $aC['sameAs'];
        }
        $authConfig[$aK] = $aC;
    }

    if (!isset($authConfig['self'])) {
        $authConfig['self'] = ['name' => "('self' name not set)", 'url' => "('self' URL not set)"];
    }

    // No authors defined — return false
    if (empty($raw)) {
        return false;
    }

    // Single string value "self"
    if ($raw === 'self') {
        return ['self' => $authConfig['self']];
    }

    // If input is string, converting to array
    $raw = is_array($raw) ? $raw : [$raw];

    $authors = [];
    foreach ($raw as $element) {

        if (is_array($element)) {
            $thisAuthor = key($element);
            $authors[$thisAuthor] = $element[$thisAuthor];
        } else {
            $thisAuthor = $element;
            $authors[$thisAuthor] = $authConfig[$thisAuthor] ?? ['name' => $element];
        }

        // TO DO: Parse name into family and given names

        if (isset($authors[$thisAuthor]['orcid'])) {
            $authors[$thisAuthor]['sameAs'] = 'https://orcid.org/' . $authors[$thisAuthor]['orcid'];
            $authors[$thisAuthor]['url'] = $authors[$thisAuthor]['url'] ?? $authors[$thisAuthor]['sameAs'];
        }

    }

    return $authors;
}

// Function for parsing a filename for a file to include.
// Returns the full path to the file if it exists, or null if it does not.

function findIncludeFile(string $includefile): ?string {
    
    // Blocking calls to files/directories starting with _ or .
    foreach (explode('/', $includefile) as $part) {
        if (str_starts_with($part, '_') || str_starts_with($part, '.')) {
            throw new ServeException("Access denied: " . $includefile, SERVE_ERROR_NOACCESS);
            return null;
        }
    }

    if (str_starts_with($includefile, 'assets/')) {
        global $assets_path;
        $file = $assets_path . substr($includefile, strpos($includefile, 'assets/') + strlen('assets/'));
    } elseif (str_starts_with($includefile, 'parent/')) {
        global $self_path;
        global $root_path;
        $file = $root_path . $self_path . '/' . substr($includefile, strpos($includefile, 'parent/') + strlen('parent/'));
    } elseif (str_starts_with($includefile, '/')) {
        global $root_path;
        $file = $root_path . ltrim($includefile, '/');
    } elseif (str_starts_with($includefile, 'includes/')) {
        global $includes_path;
        $file = $includes_path . substr($includefile, strpos($includefile, 'includes/') + strlen('includes/'));
    } else {
        global $md_path;
        $file = $md_path . $includefile;
    }

    if (!file_exists($file)) {
        throw new ServeException("File not found: " . $includefile, SERVE_ERROR_NOFILE);
        return null;
    } else {
        return $file;
    }

}

function logEvent(string $event, int $level = LOG_INFO): bool {

    // Logging events to log file
    global $config_path;
    $logFile = $config_path . 'site.log';

    $timestamp = date('Y-m-d H:i:s');
    $levelStr = match ($level) {
        LOG_INFO    => 'INFO',
        LOG_WARNING => 'WARNING',
        LOG_ERR     => 'ERROR',
        default     => 'INFO',
    };

    $logEntry = "[$timestamp] [$levelStr] $event" . PHP_EOL;
    return file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX) !== false;

}

function logEventHTML(string $event, int $level = LOG_INFO, bool $toFile = true): string {

    // Logging events to log file and returning HTML comment for debugging

    $levelStr = match ($level) {
        LOG_INFO    => 'INFO',
        LOG_WARNING => 'WARNING',
        LOG_ERR     => 'ERROR',
        default     => 'INFO',
    };

    if ($toFile) {
        $debugStr = logEvent($event, $level) ? $levelStr.": " : "WARNING: Failed to write log entry - ";
    } else {
        $debugStr = $levelStr.": ";
    }

    return "\n<!-- DEBUG/".$debugStr.htmlspecialchars($event)." -->\n";

}

function statCountPath(string $path): bool {

    // Calls statistics service API to add a hit for a given path

    $service = getConfig('stats-secret', '', 'service');

    if ($service) {

        $url = $service['url'] . $service['endpoint'] ?? '';
        $token = $service['token'] ?? '';

        if ($url && $token) {
            
            $data = [
                'no_sessions' => true,
                'hits'        => [['path' => $path]],
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
                logEvent("Statistics service request failed for path: " . $path . " - Error: " . $error, LOG_ERR);
                return false;
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return true;

        } else {
            logEvent("Statistics service URL/endpoint/token missing - cannot log hit for path: " . $path, LOG_WARNING);
            return false;
        }
    } else {
        logEvent("Statistics service not configured - cannot log hit for path: " . $path, LOG_WARNING);
        return false;
    }

}

?>