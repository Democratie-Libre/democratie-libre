<?php

namespace App\Twig;

use App\Utils\Markdown;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    private $parser;

    public function __construct(Markdown $parser)
    {
        $this->parser = $parser;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
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

    public function getName(): string
    {
        return 'markdown_extension';
    }
}
