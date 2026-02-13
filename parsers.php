<?php
/**
 * TechText - Markup Parsers
 * Converts various markup languages to different output formats
 */

class MarkupParsers {
    
    /**
     * Main conversion method
     */
    public static function convert($content, $markupType, $outputFormat) {
        // Validate input
        if (empty(trim($content))) {
            throw new Exception('Empty input content');
        }

        // First convert markup to HTML
        $html = self::toHtml($content, $markupType);
        
        // Then convert HTML to desired output format
        switch ($outputFormat) {
            case 'html':
                return self::cleanHtml($html);
            case 'plaintext':
                return self::htmlToPlainText($html);
            case 'richtext':
                return $html;
            case 'json':
                return json_encode([
                    'html' => $html,
                    'plaintext' => self::htmlToPlainText($html)
                ]);
            default:
                throw new Exception('Unsupported output format');
        }
    }

    /**
     * Convert various markup to HTML
     */
    private static function toHtml($content, $markupType) {
        switch ($markupType) {
            case 'markdown':
                return self::parseMarkdown($content);
            case 'bbcode':
                return self::parseBBCode($content);
            case 'rst':
                return self::parseReStructuredText($content);
            case 'textile':
                return self::parseTextile($content);
            case 'html':
                return $content;
            case 'wiki':
                return self::parseWiki($content);
            default:
                throw new Exception('Unsupported markup type');
        }
    }

    /**
     * Parse Markdown to HTML
     */
    private static function parseMarkdown($content) {
        // Headers
        $content = preg_replace('/^###### (.*?)$/m', '<h6>$1</h6>', $content);
        $content = preg_replace('/^##### (.*?)$/m', '<h5>$1</h5>', $content);
        $content = preg_replace('/^#### (.*?)$/m', '<h4>$1</h4>', $content);
        $content = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $content);

        // Bold and Italic
        $content = preg_replace('/\*\*\*(.+?)\*\*\*/s', '<strong><em>$1</em></strong>', $content);
        $content = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $content);
        $content = preg_replace('/___(.+?)___/s', '<strong><em>$1</em></strong>', $content);
        $content = preg_replace('/__(.+?)__/s', '<strong>$1</strong>', $content);
        $content = preg_replace('/_(.+?)_/s', '<em>$1</em>', $content);

        // Strikethrough
        $content = preg_replace('/~~(.+?)~~/s', '<del>$1</del>', $content);

        // Code blocks
        $content = preg_replace('/```(\w+)?\n(.*?)```/s', '<pre><code>$2</code></pre>', $content);
        $content = preg_replace('/`(.+?)`/s', '<code>$1</code>', $content);

        // Links
        $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $content);
        $content = preg_replace('/<([^\s>]+@[^\s>]+)>/', '<a href="mailto:$1">$1</a>', $content);
        $content = preg_replace('/<((?:https?:\/\/|www\.)[^\s>]+)>/', '<a href="$1">$1</a>', $content);

        // Images
        $content = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1">', $content);

        // Blockquotes
        $content = preg_replace('/^> (.*?)$/m', '<blockquote>$1</blockquote>', $content);
        $content = preg_replace('/<\/blockquote>\n<blockquote>/', "\n", $content);

        // Horizontal rule
        $content = preg_replace('/^(-{3,}|\*{3,}|_{3,})$/m', '<hr>', $content);

        // Lists
        $content = self::parseMarkdownLists($content);

        // Tables
        $content = self::parseMarkdownTables($content);

        // Paragraphs
        $content = self::parseParagraphs($content);

        return $content;
    }

    /**
     * Parse Markdown lists
     */
    private static function parseMarkdownLists($content) {
        // Unordered lists
        $content = preg_replace_callback('/^([\*\-\+]) (.+(?:\n(?![\*\-\+] )[^\n]+)*)/m', function($matches) {
            $items = explode("\n", $matches[2]);
            $list = "<ul>\n";
            foreach ($items as $item) {
                if (trim($item)) {
                    $list .= "<li>" . trim($item) . "</li>\n";
                }
            }
            $list .= "</ul>";
            return $list;
        }, $content);

        // Ordered lists
        $content = preg_replace_callback('/^(\d+)\. (.+(?:\n(?!\d+\. )[^\n]+)*)/m', function($matches) {
            $items = explode("\n", $matches[2]);
            $list = "<ol>\n";
            foreach ($items as $item) {
                if (trim($item)) {
                    $list .= "<li>" . trim($item) . "</li>\n";
                }
            }
            $list .= "</ol>";
            return $list;
        }, $content);

        return $content;
    }

    /**
     * Parse Markdown tables
     */
    private static function parseMarkdownTables($content) {
        return preg_replace_callback('/\|(.+)\|\n\|[-\s:]+\|\n((?:\|.+\|\n?)+)/', function($matches) {
            $headers = array_map('trim', explode('|', trim($matches[1], '|')));
            $rows = trim($matches[2]);
            
            $table = "<table>\n<thead>\n<tr>";
            foreach ($headers as $header) {
                $table .= "<th>" . trim($header) . "</th>";
            }
            $table .= "</tr>\n</thead>\n<tbody>\n";
            
            foreach (explode("\n", $rows) as $row) {
                if (trim($row)) {
                    $cells = array_map('trim', explode('|', trim($row, '|')));
                    $table .= "<tr>";
                    foreach ($cells as $cell) {
                        $table .= "<td>" . trim($cell) . "</td>";
                    }
                    $table .= "</tr>\n";
                }
            }
            $table .= "</tbody>\n</table>";
            
            return $table;
        }, $content);
    }

    /**
     * Parse BBCode to HTML
     */
    private static function parseBBCode($content) {
        $patterns = [
            '/\[b\](.+?)\[\/b\]/is' => '<strong>$1</strong>',
            '/\[i\](.+?)\[\/i\]/is' => '<em>$1</em>',
            '/\[u\](.+?)\[\/u\]/is' => '<u>$1</u>',
            '/\[s\](.+?)\[\/s\]/is' => '<del>$1</del>',
            '/\[code\](.+?)\[\/code\]/is' => '<code>$1</code>',
            '/\[code=(\w+)\](.+?)\[\/code\]/is' => '<pre><code class="language-$1">$2</code></pre>',
            '/\[quote\](.+?)\[\/quote\]/is' => '<blockquote>$1</blockquote>',
            '/\[quote=(.+?)\](.+?)\[\/quote\]/is' => '<blockquote><cite>$1</cite>$2</blockquote>',
            '/\[url\](.+?)\[\/url\]/is' => '<a href="$1">$1</a>',
            '/\[url=(.+?)\](.+?)\[\/url\]/is' => '<a href="$1">$2</a>',
            '/\[img\](.+?)\[\/img\]/is' => '<img src="$1" alt="">',
            '/\[img=(.+?)\](.+?)\[\/img\]/is' => '<img src="$1" alt="$2">',
            '/\[list\](.+?)\[\/list\]/is' => '<ul>$1</ul>',
            '/\[list=1\](.+?)\[\/list\]/is' => '<ol>$1</ol>',
            '/\[\*\](.+?)(?=\[\*\]|\[\/list\])/is' => '<li>$1</li>',
            '/\[size=(\d+)\](.+?)\[\/size\]/is' => '<span style="font-size:$1px">$2</span>',
            '/\[color=(#?[\w]+)\](.+?)\[\/color\]/is' => '<span style="color:$1">$2</span>',
            '/\[center\](.+?)\[\/center\]/is' => '<div style="text-align:center">$1</div>',
            '/\[left\](.+?)\[\/left\]/is' => '<div style="text-align:left">$1</div>',
            '/\[right\](.+?)\[\/right\]/is' => '<div style="text-align:right">$1</div>',
            '/\[email\](.+?)\[\/email\]/is' => '<a href="mailto:$1">$1</a>',
            '/\[email=(.+?)\](.+?)\[\/email\]/is' => '<a href="mailto:$1">$2</a>',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        return self::parseParagraphs($content);
    }

    /**
     * Parse reStructuredText to HTML
     */
    private static function parseReStructuredText($content) {
        // Headers with underlines
        $content = preg_replace('/(.+)\n={3,}\n/', '<h1>$1</h1>', $content);
        $content = preg_replace('/(.+)\n-{3,}\n/', '<h2>$1</h2>', $content);
        $content = preg_replace('/(.+)\n~{3,}\n/', '<h3>$1</h3>', $content);
        $content = preg_replace('/^(.+)\n\^{3,}\n/m', '<h4>$1</h4>', $content);

        // Inline markup
        $content = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $content);
        $content = preg_replace('/``(.+?)``/', '<code>$1</code>', $content);

        // Links
        $content = preg_replace('/`(.+?) <(.+?)>`_/', '<a href="$2">$1</a>', $content);

        // Code blocks
        $content = preg_replace('/::\s*\n\n((?:    .+\n?)+)/', '<pre><code>$1</code></pre>', $content);
        $content = preg_replace('/^    /m', '', $content);

        // Directives
        $content = preg_replace('/\.\. code-block:: \w+\s*\n\n((?:    .+\n?)+)/', '<pre><code>$1</code></pre>', $content);

        // Notes and warnings
        $content = preg_replace('/\.\. note::\s*\n\n((?:    .+\n?)+)/', '<div class="note"><strong>Note:</strong><br>$1</div>', $content);
        $content = preg_replace('/\.\. warning::\s*\n\n((?:    .+\n?)+)/', '<div class="warning"><strong>Warning:</strong><br>$1</div>', $content);

        // Blockquotes
        $content = preg_replace('/\n    (.+)/', '<blockquote>$1</blockquote>', $content);

        return self::parseParagraphs($content);
    }

    /**
     * Parse Textile to HTML
     */
    private static function parseTextile($content) {
        // Headers
        $content = preg_replace('/^h1\((.+?)\)\.(.*)$/m', '<h1 class="$1">$2</h1>', $content);
        $content = preg_replace('/^h1\.(.*)$/m', '<h1>$1</h1>', $content);
        $content = preg_replace('/^h2\.(.*)$/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^h3\.(.*)$/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^h4\.(.*)$/m', '<h4>$1</h4>', $content);
        $content = preg_replace('/^h5\.(.*)$/m', '<h5>$1</h5>', $content);
        $content = preg_replace('/^h6\.(.*)$/m', '<h6>$1</h6>', $content);

        // Text formatting
        $content = preg_replace('/\*(.+?)\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/_(.+?)_/', '<em>$1</em>', $content);
        $content = preg_replace('/\+(.+?)\+/', '<ins>$1</ins>', $content);
        $content = preg_replace('/-(.+?)-/', '<del>$1</del>', $content);
        $content = preg_replace('/@(.+?)@/', '<code>$1</code>', $content);
        $content = preg_replace('/%(.+?)%/', '<span style="text-decoration:underline">$1</span>', $content);
        $content = preg_replace('/\^(.+?)\^/', '<sup>$1</sup>', $content);
        $content = preg_replace('/~(.+?)~/', '<sub>$1</sub>', $content);

        // Links
        $content = preg_replace('/"(.+?)":(\S+)/', '<a href="$2">$1</a>', $content);

        // Images
        $content = preg_replace('/!(.+?)!/', '<img src="$1" alt="">', $content);
        $content = preg_replace('/!(.+?)\((.+?)\)!/', '<img src="$1" alt="$2">', $content);

        // Tables
        $content = preg_replace('/\|([^|]+)\|/', '<td>$1</td>', $content);
        $content = preg_replace('/(\|.+\|)/', '<tr>$1</tr>', $content);
        $content = preg_replace('/(<tr>.+<\/tr>)/s', '<table>$1</table>', $content);

        // Blockquotes
        $content = preg_replace('/^bq\.(.*)$/m', '<blockquote>$1</blockquote>', $content);

        // Code blocks
        $content = preg_replace('/^bc\.(.*)$/m', '<pre><code>$1</code></pre>', $content);

        // Paragraphs with classes
        $content = preg_replace('/^p\((.+?)\)\.(.*)$/m', '<p class="$1">$2</p>', $content);
        $content = preg_replace('/^p\.(.*)$/m', '<p>$1</p>', $content);

        return self::parseParagraphs($content);
    }

    /**
     * Parse Wiki markup to HTML
     */
    private static function parseWiki($content) {
        // Headers
        $content = preg_replace('/^====== (.+) ======$/m', '<h6>$1</h6>', $content);
        $content = preg_replace('/^===== (.+) =====$/m', '<h5>$1</h5>', $content);
        $content = preg_replace('/^==== (.+) ====$/m', '<h4>$1</h4>', $content);
        $content = preg_replace('/^=== (.+) ===$/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^== (.+) ==$/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^= (.+) =$/m', '<h1>$1</h1>', $content);

        // Bold and italic
        $content = preg_replace('/\'\'\'(.+?)\'\'\'/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\'\'(.+?)\'\'/', '<em>$1</em>', $content);

        // Links
        $content = preg_replace('/\[\[(.+?)\|(.+?)\]\]/', '<a href="$1">$2</a>', $content);
        $content = preg_replace('/\[\[(.+?)\]\]/', '<a href="$1">$1</a>', $content);
        $content = preg_replace('/\[(.+?)\s(.+?)\]/', '<a href="$1">$2</a>', $content);

        // External links
        $content = preg_replace('/\[(https?:\/\/\S+)\s(.+?)\]/', '<a href="$1">$2</a>', $content);

        // Lists
        $content = preg_replace('/^\*\s(.+)$/m', '<li>$1</li>', $content);
        $content = preg_replace('/^(#\s.+\n?)+/m', '<ol>$0</ol>', $content);
        $content = preg_replace('/^(\*\s.+\n?)+/m', '<ul>$0</ul>', $content);

        // Code
        $content = preg_replace('/<code>(.+?)<\/code>/s', '<code>$1</code>', $content);
        $content = preg_replace('/<pre>(.+?)<\/pre>/s', '<pre>$1</pre>', $content);

        // Tables
        $content = preg_replace('/^\{\|(.*)$/m', '<table $1>', $content);
        $content = preg_replace('/^\|\}/m', '</table>', $content);
        $content = preg_replace('/^\|-(.*)$/m', '<tr $1>', $content);
        $content = preg_replace('/^\|\+(.+)$/m', '<caption>$1</caption>', $content);
        $content = preg_replace('/^\!(.+)$/m', '<th>$1</th>', $content);
        $content = preg_replace('/^\|(.+)$/m', '<td>$1</td>', $content);

        return self::parseParagraphs($content);
    }

    /**
     * Convert HTML to plain text
     */
    private static function htmlToPlainText($html) {
        // Replace common HTML elements
        $search = [
            '/<h[1-6][^>]*>(.+?)<\/h[1-6]>/i',
            '/<p[^>]*>(.+?)<\/p>/i',
            '/<br\s*\/?>/i',
            '/<li[^>]*>(.+?)<\/li>/i',
            '/<blockquote[^>]*>(.+?)<\/blockquote>/is',
            '/<pre[^>]*>(.+?)<\/pre>/is',
            '/<code[^>]*>(.+?)<\/code>/i',
            '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.+?)<\/a>/i',
            '/<strong[^>]*>(.+?)<\/strong>/i',
            '/<em[^>]*>(.+?)<\/em>/i',
            '/<del[^>]*>(.+?)<\/del>/i',
            '/<ins[^>]*>(.+?)<\/ins>/i',
            '/<hr[^>]*>/i',
            '/<table[^>]*>(.+?)<\/table>/is',
            '/<tr[^>]*>(.+?)<\/tr>/is',
            '/<td[^>]*>(.+?)<\/td>/i',
            '/<th[^>]*>(.+?)<\/th>/i',
            '/<script[^>]*>(.+?)<\/script>/is',
            '/<style[^>]*>(.+?)<\/style>/is',
            '/<[^>]+>/'
        ];

        $replace = [
            "\n\n$1\n\n",
            "\n\n$1\n\n",
            "\n",
            "\n* $1",
            "\n> $1\n",
            "\n\n$1\n\n",
            "`$1`",
            "$2 ($1)",
            "**$1**",
            "*$1*",
            "~~$1~~",
            "$1",
            "\n----------------\n",
            "\n\n[TABLE]\n$1\n[/TABLE]\n\n",
            "\n$1",
            " | $1",
            " | $1",
            '',
            '',
            ''
        ];

        $text = preg_replace($search, $replace, $html);
        
        // Clean up whitespace
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim($text);
        
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $text;
    }

    /**
     * Clean HTML output
     */
    private static function cleanHtml($html) {
        // Remove potentially dangerous tags and attributes
        $allowedTags = '<p><br><strong><em><b><i><u><del><ins><code><pre><blockquote><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><table><thead><tbody><tr><th><td><hr><div><span>';
        
        $html = strip_tags($html, $allowedTags);
        
        // Clean attributes using preg_replace_callback instead of deprecated /e modifier
        $html = preg_replace_callback('/<(\w+)([^>]*)>/', function($matches) {
            return self::cleanAttributes('<' . $matches[1], $matches[2]) . '>';
        }, $html);
        
        return $html;
    }

    /**
     * Clean HTML attributes
     */
    private static function cleanAttributes($tag, $attrs) {
        $allowedAttrs = ['href', 'src', 'alt', 'title', 'class', 'id'];
        $cleanAttrs = '';
        
        preg_match_all('/(\w+)=["\']([^"\']+)["\']/', $attrs, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if (in_array($match[1], $allowedAttrs)) {
                $value = htmlspecialchars($match[2], ENT_QUOTES, 'UTF-8');
                if ($match[1] === 'href' || $match[1] === 'src') {
                    // Validate URL
                    if (!preg_match('/^(https?:\/\/|mailto:|#)/i', $value)) {
                        continue;
                    }
                }
                $cleanAttrs .= ' ' . $match[1] . '="' . $value . '"';
            }
        }
        
        return $tag . $cleanAttrs;
    }

    /**
     * Parse paragraphs
     */
    private static function parseParagraphs($content) {
        // Split by double newlines, but not inside certain elements
        $paragraphs = preg_split('/\n\n+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $output = '';
        
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (empty($paragraph)) continue;
            
            // Skip if already wrapped in block element
            if (preg_match('/^<(h[1-6]|p|div|blockquote|pre|ul|ol|table|hr)/i', $paragraph)) {
                $output .= $paragraph . "\n\n";
            } else {
                $output .= '<p>' . $paragraph . '</p>' . "\n\n";
            }
        }
        
        return trim($output);
    }
}
?>