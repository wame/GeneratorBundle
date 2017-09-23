<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Inflector;

use Doctrine\Common\Inflector\Inflector as DoctrineInflector;

/**
 * Class Inflector
 * @package Wame\GeneratorBundle\Inflector
 * @author Ruud Bijnen <ruud@wame.nl>
 */
class Inflector extends DoctrineInflector
{
    /**
     * @param string $word
     * @return string
     */
    public static function pluralTableize($word)
    {
        $word = static::tableize($word);
        $word = static::pluralize($word);
        return $word;
    }

    /**
     * @param string $text
     * @return string
     */
    public static function humanize($text)
    {
        return ucfirst(trim(strtolower(preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $text))));
    }

    public static function constantize($text)
    {
        $text = trim($text);
        $text = static::tableize($text);
        $text = preg_replace('/[^a-zA-Z0-9_\x7f-\xff]/', ' ', $text);
        $text = preg_replace('/[_\s-]+/', '_', $text);
        $text = trim($text, '_');
        $text = strtoupper($text);
        return $text;
    }
}
