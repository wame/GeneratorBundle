<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Wame\SensioGeneratorBundle\Inflector\Inflector;
use Wame\SensioGeneratorBundle\MetaData\MetaEntity;
use Wame\SensioGeneratorBundle\MetaData\MetaProperty;
use Wame\SensioGeneratorBundle\MetaData\MetaTrait;
use Wame\SensioGeneratorBundle\MetaData\MetaValidation;
use Wame\SensioGeneratorBundle\Model\EntityGeneratorResult;
use Wame\SensioGeneratorBundle\Twig\InflectorExtension;

class WameEntityGenerator extends DoctrineEntityGenerator
{
    protected $skeletonDirs;

    protected $behaviourTraits = [
        'softdeleteable' => '\\Gedmo\\SoftDeleteable\\Traits\\SoftDeleteableEntity',
        'timestampable'  => '\\Gedmo\\Timestampable\\Traits\\TimestampableEntity',
        'blameable'      => '\\Gedmo\\Blameable\\Traits\\BlameableEntity',
    ];

    public function setSkeletonDirs($skeletonDirs)
    {
        parent::setSkeletonDirs($skeletonDirs);
        $this->skeletonDirs[] = __DIR__.'/../Resources/skeleton';
        $this->skeletonDirs[] = __DIR__.'/../Resources';
    }

    protected function getTwigEnvironment()
    {
        $twigEnvironment = parent::getTwigEnvironment();
        $twigEnvironment->addExtension(new InflectorExtension());
        return $twigEnvironment;
    }

    public function generate(BundleInterface $bundle, $entity, $format, array $fields, InputInterface $input = null)
    {
        $entityContent = $this->render('entity/entity.php.twig', [
            'meta_entity' => $this->getMetaEntity($bundle, $entity, $fields, $input),
        ]);

        $fs = new Filesystem();
        $entityPath = $bundle->getPath().'/Entity/'.$entity.'.php';
        $fs->dumpFile($entityPath, $entityContent);

        return new EntityGeneratorResult($entityPath, null, null);
    }

    protected function getMetaEntity(BundleInterface $bundle, $entity, array $fields, InputInterface $input) : MetaEntity
    {
        $metaEntity = (new MetaEntity())
            ->setEntityName($entity)
            ->setBundleNamespace($bundle->getNamespace())
            ->setTableName(Inflector::pluralTableize($entity))
        ;
        if ($input->hasOption('behaviours')) {
            foreach ($input->getOption('behaviours') as $behaviour) {
                if (array_key_exists($behaviour, $this->behaviourTraits)) {
                    $metaEntity->addTrait(
                        (new MetaTrait())
                            ->setName($behaviour)
                            ->setNamespace($this->behaviourTraits[$behaviour])
                    );
                }
            }
        }

        foreach ($fields as $field) {
            $metaProperty = (new MetaProperty())
                ->setName($field['fieldName'])
                ->setColumnName($field['columnName'] ?? null)
                ->setType($field['type'] ?? null)
                ->setLength($field['length'] ?? null)
                ->setUnique($field['unique'] ?? false)
                ->setNullable($field['nullable'] ?? null)
                ->setScale($field['scale'] ?? null)
                ->setPrecision($field['precision'] ?? null)
                ->setTargetEntity($field['targetEntity'] ?? null)
                ->setReferencedColumnName($field['referencedColumnName'] ?? null)
            ;
            $validations = $field['validation'] ?? [];
            foreach ($validations as $validation) {
                $metaProperty->addValidation(
                    (new MetaValidation())
                        ->setType($validation['type'])
                        ->setOptions($validation['options'] ?? [])
                );
            }
            $metaEntity->addProperty($metaProperty);
        }
        return $metaEntity;
    }
}
