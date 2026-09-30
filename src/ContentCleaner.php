<?php

namespace HuseyinFiliz\Rewind;

class ContentCleaner
{
    /**
     * Convert Flarum TextFormatter XML or HTML content to clean plain text
     * by extracting text representations, replacing tags, and stripping markup.
     */
    public static function toPlainText(string $content): string
    {
        $html = $content;

        // Replace images with their alt text before stripping
        $html = preg_replace('/<img[^>]*alt="([^"]*)"[^>]*>/i', ' $1 ', $html);
        $html = preg_replace('/<img[^>]*>/i', '', $html);

        // Replace <br> and block-level closings with space
        $html = preg_replace('/<br\s*\/?>/i', ' ', $html);
        $html = preg_replace('/<\/(p|div|li|blockquote|h[1-6]|t|r)>/i', ' ', $html);

        // Strip remaining XML and HTML tags
        $text = strip_tags($html);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Remove zero-width and control characters
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);

        // Collapse whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));

        return $text;
    }

    /**
     * Count words in Flarum content, with full Unicode support including CJK.
     */
    public static function countWords(string $content): int
    {
        $text = self::toPlainText($content);

        // Preserve words with intra-word apostrophes (e.g. Pasajı'nda, don't, it's)
        $text = preg_replace('/(?<=\p{L})[\'’](?=\p{L})/u', '', $text);

        // Remove non-letter, non-number, non-space characters
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);

        // Count CJK characters individually (no spaces between words in these scripts)
        $cjkCount = 0;
        if (preg_match_all('/\p{Han}|\p{Hiragana}|\p{Katakana}|\p{Hangul}/u', $text, $matches)) {
            $cjkCount = count($matches[0]);
        }

        // Remove CJK characters before space-based splitting
        $text = preg_replace('/[\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}]/u', ' ', $text);

        // Split on whitespace for space-delimited languages
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return count($words) + $cjkCount;
    }

    /**
     * Create a plain-text excerpt from Flarum content.
     */
    public static function excerpt(string $content, int $length = 150, string $end = '...'): string
    {
        $text = self::toPlainText($content);

        if ($text === '') {
            return '';
        }

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length).$end;
    }
}
