<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Wame\GeneratorBundle\MetaData\MetaEnumType;

class WameEnumGenerator extends Generator
{
    public function generate(BundleInterface $bundle, string $className, array $enumOptions, bool $forceOverwrite): string
    {
        $metaEnum = MetaEnumType::createFromArray($enumOptions)->setBundle($bundle)->setClassName($className);
        return $this->generateByMetaEnumType($metaEnum, $forceOverwrite);
    }

    public function generateByMetaEnumType(MetaEnumType $metaEnumType, bool $forceOverwrite): string
    {
        $path = $this->getBundlePath($metaEnumType->getBundle()).'/DBAL/Types/'.$metaEnumType->getClassName().'.php';
         $content = $this->render('enum/enum.php.twig', [
             'meta_enum' => $metaEnumType,
         ]);
        static::dump($path, $content, $forceOverwrite);

        return $path;
    }
}
