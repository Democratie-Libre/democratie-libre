<?php

namespace App\Utils;

use Michelf\MarkdownExtra;

class Markdown
{
    public function toHtml($text)
    {
        return MarkdownExtra::defaultTransform((string) $text);
    }
}
