<?php

namespace App\Twig;

use App\Utils\Markdown;

class MarkdownExtension extends \Twig_Extension
{
    private $parser;

    public function __construct(Markdown $parser)
    {
        $this->parser = $parser;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'markdown',
                [$this, 'markdownToHtml'],
                ['is_safe' => ['html'], 'pre_escape' => 'html']
            ),
        ];
    }

    public function markdownToHtml($content)
    {
        return $this->parser->toHtml($content);
    }

    public function getName()
    {
        return 'markdown_extension';
    }
}
