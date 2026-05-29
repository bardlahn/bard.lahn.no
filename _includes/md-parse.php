<?php

// Requires Parsedown, placed in lib folder
require_once $lib_path . 'vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

// Functions for parsing frontmatter

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

    array_walk_recursive($yaml, function(&$value, $key) {
        $key = strtolower($key);
        if (str_contains($key, 'date') && is_int($value) && $value > 9999) {
            $value = (new DateTime())->setTimestamp($value)->format('Y-m-d');
        }
    });

    return [
        'frontmatter' => $yaml,
        'content'     => trim($matches[2]),
    ];
}

?>