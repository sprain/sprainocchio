<?php

namespace Sprainocchio\Twig;

use Sprainocchio\Util\Urlizer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UrlizerExtension extends AbstractExtension
{
    use Urlizer;

    public function getFilters() : array
    {
        return [
            new TwigFilter('urlize', $this->urlize(...)),
        ];
    }
}
