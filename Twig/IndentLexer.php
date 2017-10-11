<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Twig;

use Twig_Source;

class IndentLexer extends \Twig_Lexer
{
    public function tokenize(Twig_Source $source)
    {
        $sourceCode = str_replace(array("\r\n", "\r"), "\n", $source->getCode());

        //Remove spaces without removing newlines before {%_ or {{_ or {#
        $sourceCode = preg_replace("/(    )*{(%|{|#)_/", "{\$2", $sourceCode);

        //Inject the unindent-filter wherever 'u' is used like {%u or u%}
        $sourceCode= preg_replace("/u(-)?%}/", "%}{% filter unindent $1%}", $sourceCode);
        $sourceCode = preg_replace("/{%(-)?u/", "{%$1 endfilter %}{%", $sourceCode);

        $source = new Twig_Source($sourceCode, $source->getName(), $source->getPath());

        return parent::tokenize($source);
    }
}
