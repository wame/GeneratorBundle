<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Wame\SensioGeneratorBundle\MetaData\MetaEntity;

class WameRepositoryGenerator extends Generator
{
    use WameGeneratorTrait;

    public function generate(BundleInterface $bundle, MetaEntity $metaEntity)
    {
        $content = $this->render('repository/repository.php.twig', [
            'meta_entity' => $metaEntity,
            'bundle' => $bundle,
        ]);

        $fs = new Filesystem();
        $path = $bundle->getPath().'/Repository/'.$metaEntity->getEntityName().'Repository.php';
        $fs->dumpFile($path, $content);

        return $path;
    }
}
