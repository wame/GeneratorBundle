<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Wame\SensioGeneratorBundle\MetaData\MetaEntity;
use Wame\SensioGeneratorBundle\MetaData\MetaEntityFactory;

class WameFormGenerator extends Generator
{
    protected $registry;

    public function __construct(RegistryInterface $registry, string $rootDir)
    {
        parent::__construct($rootDir);
        $this->registry = $registry;
    }

    public function generateByBundleAndEntityName(BundleInterface $bundle, string $entityName)
    {
        $metadata = $this->registry->getManager()->getClassMetadata($bundle->getName().'\\Entity\\'.$entityName);
        $metaEntity = MetaEntityFactory::createFromClassMetadata($metadata, $bundle);
        return $this->generateByMetaEntity($metaEntity);
    }

    public function generateByMetaEntity(MetaEntity $metaEntity)
    {
        $content = $this->render('form/FormType.php.twig', [
            'meta_entity' => $metaEntity,
        ]);

        $path = $metaEntity->getBundle()->getPath().'/Form/'.$metaEntity->getEntityName().'Type.php';
        static::dump($path, $content);

        return $path;
    }
}
