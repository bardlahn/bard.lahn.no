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
                echo $defaultBefore . "\n" . $parsedown->text(replaceVars($unmarked)) . "\n" . $defaultAfter . "\n";
                $unmarked = '';
            }
            // Opening marker found
            $args    = explode(' ', trim($m[1] . $m[2]));
            $inBlock = true;
            continue;
        }

        if ($inBlock && trim($line) === '::') {
            // Closing marker found — process the block
            $content = $parsedown->text(replaceVars($blockContent));
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
                                    //      filename (path relative to assets directory)
                                    //      [wide/small] (optional, defaults to small)
                                    //      (width,height) (optional, enclosed in parantheses and comma separated)
                                    //      url (optional image link, absolute or relative)

                    $imgclass = 'image-container small';
                    $linkurl = null;
                    
                    // Checking for existence of file
                    if (file_exists($assets_path . trim($args[1]))) {
                        $imgsrc = $assets_rel_path . trim($args[1]);
                    } else {
                        $before = $defaultBefore . "<!-- DEBUG: Image file not found in path " . $assets_rel_path . trim($args[1]) . " -->";
                        $after = $defaultAfter;
                        break;
                    }

                    // Checking parameters given for image type, width/height, and link URL
                    if (trim(strtolower($args[2]))=='small') {
                        $imgclass = 'image-container small';
                        if (isset($args[3])) {
                            if (preg_match('/^\((\d+),(\d+)\)$/', $args[3], $size)) {
                                $linkurl = $args[4] ?? null;
                            } else {
                                $linkurl = $args[3];
                            }
                        }
                    } elseif(trim(strtolower($args[2]))=='wide') {
                        $imgclass = 'image-container';
                        if (isset($args[3])) {
                            if (preg_match('/^\((\d+),(\d+)\)$/', $args[3], $size)) {
                                $linkurl = $args[4] ?? null;
                            } else {
                                $linkurl = $args[3];
                            }
                        }
                    } else {
                        if (isset($args[2])) {
                            if (preg_match('/^\((\d+),(\d+)\)$/', $args[2], $size)) {
                                $linkurl = $args[3] ?? null;
                            } else {
                                $linkurl = $args[2];
                            }
                        }
                    }
                    
                    // Assembling HTML tags
                    $sizeprop = (isset($size[2])) ? 'width="' . $size[1] . '" height="' . $size[2] . '" ' : '';
                    $before = '<div class="' . $imgclass . '">';
                    $before .= ($linkurl) ? '<a href="' . $linkurl . '">' : '';
                    $before .= '<img src="' . $imgsrc . '" ' . $sizeprop . '/>';
                    $before .= ($linkurl) ? '</a>' : '';

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
        $content = $parsedown->text(replaceVars($blockContent));
        echo $defaultBefore . "\n" . $content . "\n" . $defaultAfter . "\n";
    } elseif (trim($unmarked) !== '') {
        echo $defaultBefore . "\n" . $parsedown->text(replaceVars($unmarked)) . "\n" . $defaultAfter . "\n";
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

            switch (trim($args[0])) {
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
                case 'head':
                    global $fmatter;
                    if (!empty($args[1])) {
                        if (isset($fmatter[$args[1]])) {
                            if (is_array($fmatter[$args[1]])) {
                                // The frontmatter value is an array, checking if value exists on the next YAML level
                                // NOTE: Nested arrays beyond the second level are not handled, will throw PHP error
                                if (isset($args[2]) && !empty($args[2])) {
                                    $new = (isset($fmatter[trim($args[1])][trim($args[2])])) ?
                                        $fmatter[trim($args[1])][trim($args[2])] :
                                        '<!-- DEBUG: Key "' . htmlspecialchars($args[1]) . '/' . htmlspecialchars($args[2]) . '" not available in frontmatter -->';
                                } else {
                                    $new = '<!-- DEBUG: Frontmatter key "' . htmlspecialchars($args[1]) . '" is an array, requires additional argument -->';
                                }
                            } else {
                                // The frontmatter value is not an array, returning directly
                                $new = $fmatter[trim($args[1])];
                            }
                        } else {
                            $new = '<!-- DEBUG: Key "' . htmlspecialchars($args[1]) . '" not available in frontmatter. -->';
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