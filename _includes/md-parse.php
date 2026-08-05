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

// Function for parsing a filename for a file to include.
// Returns the full path to the file if it exists, or null if it does not.

function findIncludeFile(string $includefile): ?string {
    
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

    if (file_exists($file)) {
        return $file;
    }

    return null;

}

?>