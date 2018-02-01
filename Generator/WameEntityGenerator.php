<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Generator;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Wame\GeneratorBundle\MetaData\MetaEntity;
use Wame\GeneratorBundle\MetaData\MetaProperty;
use Wame\GeneratorBundle\MetaData\MetaTrait;
use Wame\GeneratorBundle\MetaData\MetaValidation;

class WameEntityGenerator extends Generator
{
    /** @var  WameTranslationGenerator */
    protected $translationGenerator;

    /** @var  WameRepositoryGenerator */
    protected $repositoryGenerator;

    public function __construct(WameTranslationGenerator $translationGenerator, WameRepositoryGenerator $repositoryGenerator, $rootDir)
    {
        parent::__construct($rootDir);

        $this->translationGenerator = $translationGenerator;
        $this->repositoryGenerator = $repositoryGenerator;
    }

    protected static $behaviourTraits = [
        'softdeleteable' => 'Gedmo\\SoftDeleteable\\Traits\\SoftDeleteableEntity',
        'timestampable'  => 'Gedmo\\Timestampable\\Traits\\TimestampableEntity',
        'blameable'      => 'Gedmo\\Blameable\\Traits\\BlameableEntity',
    ];

    public function generate(BundleInterface $bundle = null, $entity, array $fields, array $behaviours = []): void
    {
        $metaEntity = new MetaEntity($bundle, $entity);
        foreach ($behaviours as $behaviour) {
            if (array_key_exists($behaviour, static::$behaviourTraits)) {
                $metaEntity->addTrait(
                    (new MetaTrait())
                        ->setName($behaviour)
                        ->setNamespace(static::$behaviourTraits[$behaviour])
                );
            }
        }
        foreach ($fields as $field) {
            $metaProperty = (new MetaProperty())
                ->setName($field['fieldName'])
                ->setColumnName($field['columnName'] ?? $field['fieldName'])
                ->setType($field['type'] ?? 'string')
                ->setLength($field['length'] ?? null)
                ->setDefault($field['default'] ?? null)
                ->setUnique($field['unique'] ?? false)
                ->setNullable($field['nullable'] ?? null)
                ->setScale($field['scale'] ?? null)
                ->setPrecision($field['precision'] ?? null)
                ->setTargetEntity($field['targetEntity'] ?? null)
                ->setTargetEntityNamespace($field['targetEntityNamespace'] ?? null)
                ->setMappedBy($field['mappedBy'] ?? null)
                ->setInversedBy($field['inversedBy'] ?? null)
                ->setDisplayField($field['display'] ?? false)
                ->setReferencedColumnName($field['referencedColumnName'] ?? 'id')
                ->setEnumType($field['enumType'] ?? null)
                ->setId($field['id'] ?? false)
            ;
            $validationInput = $field['validation'] ?? [];
            $validations = is_string($validationInput) ? explode(';', $validationInput) : $validationInput;

            foreach ($validations as $validationName => $validation) {
                if (is_array($validation)) {
                    $metaProperty->addValidation((new MetaValidation())
                        ->setType($validation['type'] ?? $validationName)
                        ->setOptions($validation['options'] ?? $validation)
                    );
                } else {
                    $metaProperty->addValidation((new MetaValidation())->setType(is_bool($validation) || !$validation ? $validationName : $validation));
                }
            }
            $metaEntity->addProperty($metaProperty);
        }
        $this->generateByMetaEntity($metaEntity);
    }

    public function generateByMetaEntity(MetaEntity $metaEntity, $includeRepo = true): void
    {
        $this->addIdFieldIfMissing($metaEntity);
        $entityContent = $this->render('entity/entity.php.twig', [
            'meta_entity' => $metaEntity,
        ]);
        $bundlePath = $this->getBundlePath($metaEntity->getBundle());
        $entityPath = $bundlePath.'/Entity/'.$metaEntity->getDirectory('/').$metaEntity->getEntityName().'.php';
        static::dump($entityPath, $entityContent);

        $includeRepo ? $this->repositoryGenerator->generateByMetaEntity($metaEntity) : null;

        $this->translationGenerator->updateByMetaEntity($metaEntity);
    }

    protected function addIdFieldIfMissing(MetaEntity $metaEntity): void
    {
        foreach ($metaEntity->getProperties() as $property) {
            if ($property->isId()) {
                return;
            }
        }
        $idProperty = (new MetaProperty())->setName('id')->setId(true)->setType('integer')->setEntity($metaEntity);
        $propertyArray = array_merge([$idProperty], $metaEntity->getProperties()->toArray());
        $propertyCollection = new ArrayCollection($propertyArray);
        $metaEntity->setProperties($propertyCollection);
    }
}
