<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Wame\SensioGeneratorBundle\MetaData\MetaEnumType;

class WameEnumGenerator extends Generator
{
    public function generate(BundleInterface $bundle, string $className, array $enumOptions, bool $forceOverwrite): string
    {
        $metaEnum = MetaEnumType::createFromArray($enumOptions)->setBundle($bundle)->setClassName($className);
        return $this->generateByMetaEnumType($metaEnum, $forceOverwrite);
    }

    public function generateByMetaEnumType(MetaEnumType $metaEnumType, bool $forceOverwrite): string
    {
        $path = $metaEnumType->getBundle()->getPath().'/DBAL/Types/'.$metaEnumType->getClassName().'.php';
         $content = $this->render('enum/enum.php.twig', [
             'meta_enum' => $metaEnumType,
         ]);
        static::dump($path, $content, $forceOverwrite);

        return $path;
    }
}
