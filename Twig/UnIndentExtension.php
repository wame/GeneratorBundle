<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Twig;

class UnIndentExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('unindent', [$this, 'unindent']),
        ];
    }

    public function unindent($str)
    {
        return preg_replace("/ {4}(\s*\S+)/", "$1", $str);
    }
}