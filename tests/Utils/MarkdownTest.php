<?php

namespace Tests\Utils;

use App\Utils\Markdown;
use PHPUnit\Framework\TestCase;

class MarkdownTest extends TestCase
{
    public function testItConvertsMarkdownToHtml()
    {
        $this->assertSame("<p><strong>gras</strong></p>\n", (new Markdown())->toHtml('**gras**'));
    }

    /**
     * Some stored texts (e.g. the motivation of an article) are NULL in the
     * database: they must render as an empty string, not crash the page.
     */
    public function testItAcceptsNull()
    {
        $this->assertSame('', trim((new Markdown())->toHtml(null)));
    }
}
