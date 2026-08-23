<?php

// Requires Parsedown, placed in lib folder
require_once $lib_path . 'vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

// Function for parsing frontmatter

function parseMDFile(string $filePath): array {
    if (!file_exists($filePath)) {
        throw new RuntimeException("File not found: $filePath");
    }

    $content = file_get_contents($filePath);

    if (!preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)/s', $content, $matches)) {
        return [
            'frontmatter' => [],
            'content'     => $content,
        ];
    }

    $yaml = Yaml::parse($matches[1]);

    return [
        'frontmatter' => $yaml,
        'content'     => trim($matches[2]),
    ];
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


?>