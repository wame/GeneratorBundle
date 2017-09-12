<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Wame\SensioGeneratorBundle\MetaData\MetaEnumType;

class WameEnumGenerator extends Generator
{
    use WameGeneratorTrait;

    public function generate(BundleInterface $bundle, string $className, array $enumOptions): string
    {
        $metaEnum = MetaEnumType::createFromArray($enumOptions)->setBundle($bundle)->setClassName($className);
        return $this->generateFromMetaEnumType($metaEnum);
    }

    public function generateFromMetaEnumType(MetaEnumType $metaEnumType): string
    {
        $fs = new Filesystem();

        $path = $metaEnumType->getBundle()->getPath().'/DBAL/Types/'.$metaEnumType->getClassName().'.php';
         $content = $this->render('enum/enum.php.twig', [
             'meta_enum' => $metaEnumType,
         ]);
        $fs->dumpFile($path, $content);

        return $path;
    }
}
