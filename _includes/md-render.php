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
                echo $defaultBefore . "\n" . $parsedown->text($unmarked) . "\n" . $defaultAfter . "\n";
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

                case 'insert':      // ::insert block - takes arguments:
                                    // name=var ('name' is replaced in the block with var)
                                    //          (var can be url_self, url_assets, url_parent, title, lang, lang_other)

                    $before = $defaultBefore;
                    $after = $defaultAfter;

                    foreach (array_slice($args, 1) as $arg) {
                        $arg = trim(strtolower($arg));
                        switch ($arg) {
                            case 'url_self':
                                global $self_url;
                                $content = str_replace(':$'.$arg.':', $self_url, $content);
                                break;
                            case 'url_assets':
                                global $assets_rel_path;
                                $content = str_replace(':$'.$arg.':', $assets_rel_path, $content);
                                break;
                            // case 'url_parent':   NOT IMPLEMENTED
                            //    break;
                            case 'title':
                                global $self_title;
                                $content = str_replace(':$'.$arg.':', $self_title, $content);
                                break;
                            case 'lang':
                                global $lang;
                                $content = str_replace(':$'.$arg.':', $lang, $content);
                                break;
                            case 'lang_other':
                                global $otherLang;
                                $content = str_replace(':$'.$arg.':', $otherLang, $content);
                                break;
                        }
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
                echo $before . "\n" . $content . "\n" . $after . "\n";
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
        echo $defaultBefore . "\n" . $content . "\n" . $defaultAfter . "\n";
    } elseif (trim($unmarked) !== '') {
        echo $defaultBefore . "\n" . $parsedown->text($unmarked) . "\n" . $defaultAfter . "\n";
    }

    return true;
}

?>