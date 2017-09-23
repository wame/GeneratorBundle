<?php
declare(strict_types = 1);

namespace Wame\GeneratorBundle\Twig;

use Wame\GeneratorBundle\Inflector\Inflector;

class InflectorExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('tabalize', [$this, 'tabalize']),
            new \Twig_SimpleFilter('pluralize', [$this, 'pluralize']),
            new \Twig_SimpleFilter('singularize', [$this, 'singularize']),
            new \Twig_SimpleFilter('camelize', [$this, 'camelize']),
            new \Twig_SimpleFilter('classify', [$this, 'classify']),
            new \Twig_SimpleFilter('humanize', [$this, 'humanize']),
        ];
    }

    public function tabalize($string)
    {
        return Inflector::tableize($string);
    }

    public function pluralize($string)
    {
        return Inflector::pluralize($string);
    }

    public function singularize($string)
    {
        return Inflector::singularize($string);
    }

    public function camelize($string)
    {
        return Inflector::camelize($string);
    }

    public function classify($string)
    {
        return Inflector::classify($string);
    }

    public function humanize($string)
    {
        return Inflector::humanize($string);
    }
}
