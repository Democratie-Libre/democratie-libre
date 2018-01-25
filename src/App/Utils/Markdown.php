<?php

namespace App\Utils;

use Aptoma\Twig\Extension\MarkdownEngine;
use Aptoma\Twig\Extension\MarkdownExtension;

class Markdown
{
    private $parser;

    public function __construct()
    {
        $engine = new MarkdownEngine\MichelfMarkdownEngine();

        $this->parser = new MarkdownExtension($engine); 
    }

    public function toHtml($text)
    {
        $html = $this->parser->parseMarkdown($text);

        return $html;
    }
}
