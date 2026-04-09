<?php

namespace HuseyinFiliz\Rewind;

use Flarum\Formatter\Formatter;

class ContentCleaner
{
    /**
     * Convert Flarum TextFormatter XML content to clean plain text
     * by rendering through s9e then stripping HTML.
     */
    public static function toPlainText(string $content): string
    {
        try {
            /** @var Formatter $formatter */
            $formatter = resolve(Formatter::class);

            // Render XML to HTML via s9e TextFormatter
            $html = $formatter->render($content);
        } catch (\Throwable $e) {
            // Fallback if formatter fails
            $html = $content;
        }

        // Replace images with placeholder before stripping
        $html = preg_replace('/<img[^>]*alt="([^"]*)"[^>]*>/i', ' $1 ', $html);
        $html = preg_replace('/<img[^>]*>/i', '', $html);

        // Replace <br> and block-level closings with space
        $html = preg_replace('/<br\s*\/?>/i', ' ', $html);
        $html = preg_replace('/<\/(p|div|li|blockquote|h[1-6])>/i', ' ', $html);

        // Strip remaining tags
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
    public static function excerpt(string $content, int $length = 150): string
    {
        $text = self::toPlainText($content);

        if ($text === '') {
            return '';
        }

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length).'…';
    }
}
