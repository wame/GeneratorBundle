<?php
declare(strict_types = 1);

namespace Wame\SensioGeneratorBundle\Twig;

use Wame\SensioGeneratorBundle\Inflector\Inflector;

class InflectorExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('tabalize', [$this, 'tabalize']),
            new \Twig_SimpleFilter('pluralize', [$this, 'pluralize']),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('nl', [$this, 'newline'])
        ];
    }

    public function newline($spaces)
    {
        $newlineWithSpace = '\n';
        for ($i=0; $i < $spaces; $i++) {
            $newlineWithSpace .= ' ';
        }
        return $newlineWithSpace;
    }

    public function tabalize($string)
    {
        return Inflector::tableize($string);
    }

    public function pluralize($string)
    {
        return Inflector::pluralize($string);
    }
}
