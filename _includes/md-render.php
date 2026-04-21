<?php

// Function for rendering markdown and parsing special blocks
// NOTE: Must be called from body.php
//
// Syntax for special blocks:
//   ::block-id arg1 arg2 arg3 ...
//   (content)
//   ::
//

function renderMDContent(string $text) {
    $defaultBefore = '<div class="content">';
    $defaultAfter  = '</div>';
    global $assets_path;
    global $assets_rel_path;
    global $root_path;

    $parsedown = new Parsedown();
    $parsedown->setMarkupEscaped(false);
    $parsedown->setSafeMode(false);

    // Normalise line endings
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\r", "\n", $text);

    $lines        = explode("\n", $text);
    // $output       = '';
    $inBlock      = false;
    $blockContent = '';
    $args         = [];
    $unmarked     = '';

    foreach ($lines as $line) {
 
        if (!$inBlock && preg_match('/^::(\S+)((?:[ \t]+\S+)*)[ \t]*$/', $line, $m)) {
            // Flush any accumulated unmarked text
            if (trim($unmarked) !== '') {
                echo $defaultBefore . "\n" . replaceVars($parsedown->text($unmarked)) . "\n" . $defaultAfter . "\n";
                $unmarked = '';
            }
            // Opening marker found
            $args    = explode(' ', trim($m[1] . $m[2]));
            $inBlock = true;
            continue;
        }

        if ($inBlock && trim($line) === '::') {
            // Closing marker found — process the block
            $content = $parsedown->text($blockContent);
            $before  = '';
            $after   = '';

            // Case switch based on block-id
            switch ($args[0]) {

                case 'sidebar':     // ::sidebar block - takes no arguments

                    $before = '<div class="sidebar"><div class="sidebar-text">';
                    $after  = '</div></div>';
                    break;

                case 'quote':       // ::quote block - takes no arguments

                    $before = '<div class="blockquote-container"><blockquote>';
                    $after  = '</blockquote></div>';
                    break;

                case 'image':       // ::image block - takes arguments:
                                    //      [wide/small] (optional, defaults to small)
                                    //      img-path (path relative to assets directory)

                    $imgtag = "";

                    // Checking parameters given for image type and file
                    if (trim(strtolower($args[1]))=='small') {
                        $imgclass = 'image-container small';
                        // Checking if image file exists
                        if (file_exists($assets_path . trim($args[2]))) {
                            $imgtag = '<img src="' . $assets_rel_path . trim($args[2]) . '" >';
                        }
                    } elseif(trim(strtolower($args[1]))=='wide') {
                        $imgclass = 'image-container';
                        // Checking if image file exists
                        if (file_exists($assets_path . trim($args[2]))) {
                            $imgtag = '<img src="' . $assets_rel_path . trim($args[2]) . '">';
                        }
                    } else {
                        $imgclass = 'image-container small';
                        // Using arg-1 as image file, checking if file exists
                        if (file_exists($assets_path . trim($args[1]))) {
                            $imgtag = '<img src="' . $assets_rel_path . trim($args[1]) . '">';
                        }
                    }
                    
                    $before = '<div class="' . $imgclass . '">';
                    if ($imgtag) { $before .= $imgtag; }

                    // If block contains md text, render the text as an image caption
                    if (trim($blockContent)) {
                        $content = str_replace(['<p>', '</p>'], ['<p><span>', '</span></p>'], $content);
                        $before .= '<div class="image-caption">';
                        $after = '</div></div>';
                    } else {
                        $after = '</div>';
                    }
                    
                    break;

                case 'include':     // ::include block - takes arguments:
                                    // filename (required - file to be included, path relative to MD file unless /includes/ or /assets/ is given)
                                    // [php/md/raw] (optional parse mode - defaults to raw)

                    global $md_path;
                    $includefile = trim($args[1]);

                    if (str_starts_with($includefile, '/includes/')) {
                        global $includes_path;
                        $includefile = $includes_path . substr($includefile, strpos($includefile, '/includes/') + strlen('/includes/'));
                    } elseif (str_starts_with($includefile, '/assets/')) {
                        global $assets_path;
                        $includefile = $assets_path . substr($includefile, strpos($includefile, '/assets/') + strlen('/assets/'));
                    } else {
                        $includefile = $md_path . $includefile;
                    }
                    
                    $parseMode = trim(strtolower($args[2])) ?? '';
                    $before = $defaultBefore;
                    $after = $defaultAfter;

                    if (file_exists($includefile)) {
                        // Include file!
                        
                        switch ($parseMode) {

                            case 'php':
                                include $includefile;
                                break;

                            case 'md':
                                $parsed = parseMDFile($includefile);
                                renderMDContent($parsed['content']);
                                break;

                            default:
                                $rawfile = file_get_contents($includefile);
                                echo $rawfile;
                                break;

                        }

                    } else {
                        $before = $defaultBefore . "<!-- DEBUG: Include-file not found in path " . $includefile . " -->";
                    }

                    break;

                // (Further block types can be added here)

                // Unknown or missing block-id is treated as unmarked block
                default:
                    $before = $defaultBefore;
                    $after = $defaultAfter;
                    break;
            }

            // Assembling block output
            if (trim($content)) {
                echo $before . "\n" . replaceVars($content) . "\n" . $after . "\n";
            }
            $inBlock      = false;
            $blockContent = '';
            continue;
        }

        if ($inBlock) {
            $blockContent .= $line . "\n";
        } else {
            $unmarked .= $line . "\n";
        }
    }

    // Flush any remaining content
    if ($inBlock && trim($blockContent) !== '') {
        // Block was never closed — render it anyway
        $content = $parsedown->text($blockContent);
        echo $defaultBefore . "\n" . replaceVars($content) . "\n" . $defaultAfter . "\n";
    } elseif (trim($unmarked) !== '') {
        echo $defaultBefore . "\n" . replaceVars($parsedown->text($unmarked)) . "\n" . $defaultAfter . "\n";
    }

    return true;
}


// Helper function to replace variables given in the MD content
//   Variables can be inserted in MD on the form :$variable:
//   Variables implemented so far:
//   url_self, url_assets, url_parent, title, lang, lang_other
//   head/ARG returnerer verdien av ARG fra frontmatter

function replaceVars(string $input): string {
    return preg_replace_callback(
        '/:\$([^:]+):/',
        function (array $matches): string {
            $args = explode('/', $matches[1]);
            $new = '';

            switch ($args[0]) {
                case 'url_self':
                    global $self_url;
                    $new = $self_url;
                    break;
                case 'url_assets':
                    global $assets_rel_path;
                    $new = $assets_rel_path;
                    break;
                // case 'url_parent':   NOT IMPLEMENTED
                case 'title':
                    global $self_title;
                    $new = $self_title;
                    break;
                case 'lang':
                    global $lang;
                    $new = $lang;
                    break;
                case 'lang_other':
                    global $otherLang;
                    $new = $otherLang;
                    break;
                case 'head'
                    global $content;
                    if (isset($args[1])) {
                        if (isset($content['frontmatter'][$args[1]])) {
                            $new = $content['frontmatter'][$args[1]];
                        } else {
                            $new = '<!-- DEBUG: Key "' . htmlspecialchars($args[1]) . '" not found in frontmatter -->';
                        }
                    } else {
                        $new = '<!-- DEBUG: Variable "head" invoked but no frontmatter key provided -->';
                    }
                    break;

                default:
                    $new = '<!-- DEBUG: Unknown variable "' . htmlspecialchars($matches[1]) . '" -->';
                    break;

                    }

            return $new;
        },
        $input
    );
}

?>