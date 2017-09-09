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

class WameEntityGenerator extends DoctrineEntityGenerator
{
    use WameGeneratorTrait;

    protected $behaviourTraits = [
        'softdeleteable' => 'Gedmo\\SoftDeleteable\\Traits\\SoftDeleteableEntity',
        'timestampable'  => 'Gedmo\\Timestampable\\Traits\\TimestampableEntity',
        'blameable'      => 'Gedmo\\Blameable\\Traits\\BlameableEntity',
    ];

    public function generate(BundleInterface $bundle, $entity, $format, array $fields, InputInterface $input = null)
    {
        $metaEntity = $this->getMetaEntity($bundle, $entity, $fields, $input);
        $entityContent = $this->render('entity/entity.php.twig', [
            'meta_entity' => $metaEntity,
        ]);

        $fs = new Filesystem();
        $entityPath = $bundle->getPath().'/Entity/'.$entity.'.php';
        $fs->dumpFile($entityPath, $entityContent);

        $repositoryPath = $this->getRepositoryGenerator()->generate($bundle, $metaEntity);

        return new EntityGeneratorResult($entityPath, $repositoryPath, null);
    }

    protected function getRepositoryGenerator()
    {
        return new WameRepositoryGenerator();
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

        $displayField = $input->hasOption('display-field') ? $input->getOption('display-field') : null;

        if (!array_key_exists('id', $fields)) {
            array_unshift($fields, [
                'fieldName' => 'id',
                'id' => true,
            ]);
        }

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
