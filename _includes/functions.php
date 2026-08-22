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

// Helper function to extract specific frontmatter fields from a 
// markdown file without fully parsing it

function getFrontmatterFields(string $filePath, array $fields): array
{
    $result = [];
    foreach ($fields as $field) {
        $result[$field] = null;
    }

    if (empty($fields)) {
        // No fields specified
        return $result;
    }

    $handle = @fopen($filePath, 'r');
    if ($handle === false) {
        return $result;
    }

    $firstLine = fgets($handle);
    if ($firstLine === false || trim($firstLine) !== '---') {
        // No frontmatter block found
        fclose($handle);
        return $result;
    }

    $patterns = [];
    foreach ($fields as $field) {
        $patterns[$field] = '/^' . preg_quote($field, '/') . '\s*:\s*(.*)$/';
    }

    $remaining = count($fields);

    while (($line = fgets($handle)) !== false) {
        $trimmed = trim($line);

        // End of frontmatter
        if ($trimmed === '---' || $trimmed === '...') {
            break;
        }

        // Try to match each field
        foreach ($patterns as $field => $pattern) {
            if ($result[$field] === null && preg_match($pattern, $line, $m)) {
                $result[$field] = stripFrontmatterQuotes($m[1]);
                $remaining--;

                if ($remaining === 0) {
                    break 2;
                }
            }
        }
    }

    fclose($handle);
    return $result;
}


// Helper function to remove surrounding quotes from a frontmatter value

function stripFrontmatterQuotes(string $value): string
{
    $value = trim($value);

    if (strlen($value) >= 2) {
        $first = $value[0];
        $last = $value[strlen($value) - 1];
        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            return substr($value, 1, -1);
        }
    }

    return $value;
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
    echo "Logging event: $logEntry" . PHP_EOL; // Debugging output
    echo "Logging to file: $logFile" . PHP_EOL; // Debugging output
    $write = file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    echo "Write result: " . ($write !== false ? "Success" : "Failure") . PHP_EOL; // Debugging output
    return $write !== false;

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

?>