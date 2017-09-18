<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Wame\SensioGeneratorBundle\MetaData\MetaEntity;

class WameRepositoryGenerator extends Generator
{
    use WameGeneratorTrait;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function generateByMetaEntity(MetaEntity $metaEntity)
    {
        $content = $this->render('repository/repository.php.twig', [
            'meta_entity' => $metaEntity,
        ]);

        $fs = new Filesystem();
        $path = $metaEntity->getBundle()->getPath().'/Repository/'.$metaEntity->getEntityName().'Repository.php';
        $fs->dumpFile($path, $content);

        return $path;
    }
}
