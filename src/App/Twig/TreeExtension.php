<?php

namespace App\Twig;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use App\Entity\Theme;

class TreeExtension extends \Twig_Extension
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('tree', [$this, 'treeFunction']),
        ];
    }

    public function treeFunction(Theme $theme)
    {
        $children = $theme->getChildren();
        $themeRoute = $this->router->generate('theme_show', ['slug' => $theme->getSlug()]);
        $themeTitle = $theme->getTitle();
        $noChildFormat = '<li class="noChild"><a href="%s">%s</a></li>';
        $closedFormat = '<li class="closed"><a href="%s">%s</a><ul class="hidden">';

        if (count($children) == 0) {
            $tree = sprintf($noChildFormat, $themeRoute, $themeTitle);
        } else {
            $tree = sprintf($closedFormat, $themeRoute, $themeTitle);

            foreach ($children as $child) {
                $tree = $tree.$this->treeFunction($child);
            }

            $tree = $tree.'</ul></li>';
        }

        return $tree;
    }

    public function getName()
    {
        return 'treeExtension';
    }
}
