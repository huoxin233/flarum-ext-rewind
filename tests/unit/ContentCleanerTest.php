<?php

namespace HuseyinFiliz\Rewind\Tests\unit;

use HuseyinFiliz\Rewind\ContentCleaner;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ContentCleanerTest extends TestCase
{
    #[Test]
    public function test_to_plain_text_strips_html_and_xml_tags(): void
    {
        $xml = '<t><p>This is a <b>formatted</b> post with a <URL url="https://flarum.org">link</URL>.</p></t>';
        $plain = ContentCleaner::toPlainText($xml);

        $this->assertEquals('This is a formatted post with a link.', $plain);
    }

    #[Test]
    public function test_to_plain_text_extracts_image_alt_text(): void
    {
        $content = '<p>Check out this chart: <img src="chart.png" alt="Sales Growth 2026"> and details.</p>';
        $plain = ContentCleaner::toPlainText($content);

        $this->assertEquals('Check out this chart: Sales Growth 2026 and details.', $plain);
    }

    #[Test]
    public function test_to_plain_text_decodes_html_entities(): void
    {
        $content = '<p>Rock &amp; Roll &gt; Pop &lt; Jazz &quot;Music&quot;</p>';
        $plain = ContentCleaner::toPlainText($content);

        $this->assertEquals('Rock & Roll > Pop < Jazz "Music"', $plain);
    }

    #[Test]
    public function test_to_plain_text_removes_zero_width_characters_and_collapses_whitespace(): void
    {
        $content = "<p>Multiple \t\n spaces   and \u{200B}zero-width\u{FEFF} characters.</p>";
        $plain = ContentCleaner::toPlainText($content);

        $this->assertEquals('Multiple spaces and zero-width characters.', $plain);
    }

    #[Test]
    public function test_count_words_standard_text(): void
    {
        $content = '<p>The quick brown fox jumps over the lazy dog.</p>';
        $count = ContentCleaner::countWords($content);

        $this->assertEquals(9, $count);
    }

    #[Test]
    public function test_count_words_with_turkish_characters(): void
    {
        $content = '<p>Şemsi Paşa Pasajı\'nda üç tas has hoşaf içildi.</p>';
        $count = ContentCleaner::countWords($content);

        $this->assertEquals(8, $count);
    }

    #[Test]
    public function test_count_words_cjk_characters(): void
    {
        // 4 CJK characters + 2 English words ("Hello" and "World")
        $content = '<p>Hello 世界 测试 World</p>';
        $count = ContentCleaner::countWords($content);

        $this->assertEquals(6, $count);
    }

    #[Test]
    public function test_excerpt_truncates_long_content(): void
    {
        $content = '<p>'.str_repeat('Long content to test excerpt. ', 10).'</p>';
        $excerpt = ContentCleaner::excerpt($content, 50);

        $this->assertLessThanOrEqual(53, mb_strlen($excerpt)); // 50 + '...'
        $this->assertStringEndsWith('...', $excerpt);
    }

    #[Test]
    public function test_excerpt_returns_full_content_if_short(): void
    {
        $content = '<p>Short post</p>';
        $excerpt = ContentCleaner::excerpt($content, 50);

        $this->assertEquals('Short post', $excerpt);
    }

    #[Test]
    public function test_excerpt_returns_empty_string_for_empty_content(): void
    {
        $this->assertEquals('', ContentCleaner::excerpt(''));
    }
}
