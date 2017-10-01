<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Generator;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Wame\GeneratorBundle\MetaData\MetaEntity;
use Wame\GeneratorBundle\MetaData\MetaEntityFactory;

class WameFormGenerator extends Generator
{
    protected $registry;

    public function __construct(RegistryInterface $registry, string $rootDir)
    {
        parent::__construct($rootDir);
        $this->registry = $registry;
    }

    public function generateByBundleAndEntityName(BundleInterface $bundle, string $entityName, $allowOverride = false): bool
    {
        $metadata = $this->registry->getManager()->getClassMetadata($bundle->getName().'\\Entity\\'.$entityName);
        $metaEntity = MetaEntityFactory::createFromClassMetadata($metadata, $bundle);
        return $this->generateByMetaEntity($metaEntity, $allowOverride);
    }

    public function generateByMetaEntity(MetaEntity $metaEntity, $allowOverride = false): bool
    {
        $content = $this->render('form/FormType.php.twig', [
            'meta_entity' => $metaEntity,
        ]);

        $path = $metaEntity->getBundle()->getPath().'/Form/'.$metaEntity->getDirectory('/').$metaEntity->getEntityName().'Type.php';

        return static::dump($path, $content, $allowOverride) !== false;
    }
}
