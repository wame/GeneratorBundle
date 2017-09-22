<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Wame\SensioGeneratorBundle\MetaData\MetaEntity;

class WameRepositoryGenerator extends Generator
{
    public function generateByMetaEntity(MetaEntity $metaEntity)
    {
        $content = $this->render('repository/repository.php.twig', [
            'meta_entity' => $metaEntity,
        ]);

        $path = $metaEntity->getBundle()->getPath().'/Repository/'.$metaEntity->getEntityName().'Repository.php';
        static::dump($path, $content);

        return $path;
    }
}
