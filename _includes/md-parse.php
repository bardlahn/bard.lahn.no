<?php

// Requires Parsedown, placed in _includes folder
require $lib_path . 'vendor/autoload.php';
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

    return [
        'frontmatter' => Yaml::parse($matches[1]),
        'content'     => trim($matches[2]),
    ];
}

?>