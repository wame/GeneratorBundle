<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Generator;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Wame\SensioGeneratorBundle\Inflector\Inflector;
use Wame\SensioGeneratorBundle\MetaData\MetaEntity;
use Wame\SensioGeneratorBundle\MetaData\MetaProperty;
use Wame\SensioGeneratorBundle\MetaData\MetaTrait;
use Wame\SensioGeneratorBundle\MetaData\MetaValidation;
use Wame\SensioGeneratorBundle\Model\EntityGeneratorResult;

class WameEntityGenerator extends DoctrineEntityGenerator
{
    use WameGeneratorTrait;

    public function __construct(?Filesystem $filesystem = null, ?RegistryInterface $registry = null)
    {
        if ($filesystem && $registry) {
            parent::__construct($filesystem, $registry);
        }
    }

    protected $behaviourTraits = [
        'softdeleteable' => 'Gedmo\\SoftDeleteable\\Traits\\SoftDeleteableEntity',
        'timestampable'  => 'Gedmo\\Timestampable\\Traits\\TimestampableEntity',
        'blameable'      => 'Gedmo\\Blameable\\Traits\\BlameableEntity',
    ];

    public function generate(BundleInterface $bundle, $entity, $format, array $fields, InputInterface $input = null)
    {
        $metaEntity = $this->getMetaEntity($bundle, $entity, $fields, $input);
        return $this->generateByMetaEntity($metaEntity);
    }

    public function generateByMetaEntity(MetaEntity $metaEntity, $includeRepo = true)
    {
        $this->addIdFieldIfMissing($metaEntity);
        $fs = new Filesystem();
        $entityContent = $this->render('entity/entity.php.twig', [
            'meta_entity' => $metaEntity,
        ]);
        $entityPath = $metaEntity->getBundle()->getPath().'/Entity/'.$metaEntity->getEntityName().'.php';
        $fs->dumpFile($entityPath, $entityContent);

        $repositoryPath = $includeRepo ? $this->getRepositoryGenerator()->generate($metaEntity) : null;

        return new EntityGeneratorResult($entityPath, $repositoryPath, null);
    }

    protected function addIdFieldIfMissing(MetaEntity $metaEntity)
    {
        foreach ($metaEntity->getProperties() as $property) {
            if ($property->isId()) {
                return;
            }
        }
        $idProperty = (new MetaProperty())->setName('id')->setId(true);
        $propertyArray = array_merge([$idProperty], $metaEntity->getProperties()->toArray());
        $propertyCollection = new ArrayCollection($propertyArray);
        $metaEntity->setProperties($propertyCollection);
    }

    protected function getRepositoryGenerator()
    {
        return new WameRepositoryGenerator();
    }

    protected function getMetaEntity(BundleInterface $bundle, $entity, array $fields, InputInterface $input) : MetaEntity
    {
        $metaEntity = (new MetaEntity())
            ->setEntityName($entity)
            ->setBundle($bundle)
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

        $displayField = $input->hasOption('display-field') ? $input->getOption('display-field') : null;

        foreach ($fields as $field) {
            $isDisplayField = Inflector::camelize($displayField) === Inflector::camelize($field['fieldName']) || isset($field['displayField']);
            $metaProperty = (new MetaProperty())
                ->setName($field['fieldName'])
                ->setColumnName($field['columnName'] ?? null)
                ->setType($field['type'] ?? 'string')
                ->setLength($field['length'] ?? null)
                ->setUnique($field['unique'] ?? false)
                ->setNullable($field['nullable'] ?? null)
                ->setScale($field['scale'] ?? null)
                ->setPrecision($field['precision'] ?? null)
                ->setTargetEntity($field['targetEntity'] ?? null)
                ->setMappedBy($field['mappedBy'] ?? null)
                ->setInversedBy($field['inversedBy'] ?? null)
                ->setDisplayField($isDisplayField)
                ->setReferencedColumnName($field['referencedColumnName'] ?? 'id')
                ->setEnumType($field['enumType'] ?? null)
                ->setId($field['id'] ?? false)
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
