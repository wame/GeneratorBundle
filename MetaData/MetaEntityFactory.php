<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\MetaData;

use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * Class MetaEntityFactory
 *
 * Converts ClassMetadata to MetaEntity.
 * Note: not all information is converted. Some information may be lost:
 *  - the order of properties: relation-properties (many2one, one2many, etc) will processed after 'normal' properties.
 *  - Enums are not recognized and will be treated as strings
 *  - Validations are not recognized.
 *  - displayField (in toString method) won't be set
 *
 * @package Wame\SensioGeneratorBundle\MetaData
 */
class MetaEntityFactory
{
    public static function createFromClassMetadata(ClassMetadata $classMetadata, BundleInterface $bundle)
    {
        $reflectionClass = $classMetadata->getReflectionClass();
        $reflectionClass = $reflectionClass ?: new \ReflectionClass($classMetadata->name);

        $entityMetadata = (new MetaEntity())
            ->setBundle($bundle)
            ->setEntityName($reflectionClass->getShortName())
            ->setTableName($classMetadata->getTableName())
        ;
        static::setInterfaces($entityMetadata, $reflectionClass);
        static::setTraits($entityMetadata, $reflectionClass);

        static::setFields($entityMetadata, $classMetadata, $reflectionClass);

        static::setAssociationFields($entityMetadata, $classMetadata, $reflectionClass);


        return $entityMetadata;
    }

    protected static function setFields(MetaEntity $metaEntity, ClassMetadata $classMetadata, \ReflectionClass $reflectionClass)
    {
        foreach ($classMetadata->fieldMappings as $fieldName => $fieldMapping) {
            if (static::isTraitField($reflectionClass, $fieldName)) {
                continue;
            }

            $metaEntity->addProperty(
                (new MetaProperty())
                    ->setId($fieldMapping['id'] ?? false)
                    ->setName($fieldName)
                    ->setType($fieldMapping['type'] ?? 'string')
                    ->setNullable($fieldMapping['nullable'] ?? false)
                    ->setLength(isset($fieldMapping['length']) ? (int) $fieldMapping['length'] : null)
                    ->setScale(isset($fieldMapping['scale']) ? (int) $fieldMapping['scale'] : null)
                    ->setPrecision(isset($fieldMapping['precision']) ? (int) $fieldMapping['precision'] : null)
            );
        }
    }

    protected static function setAssociationFields(MetaEntity $metaEntity, ClassMetadata $classMetadata, \ReflectionClass $reflectionClass)
    {
        foreach ($classMetadata->getAssociationMappings() as $fieldName => $associationMapping) {
            if (static::isTraitField($reflectionClass, $fieldName)) {
                continue;
            }

            $joinColumns = $associationMapping['joinColumns'] ?? [];
            $joinColumn = empty($associationMapping) ? [] : reset($joinColumns);

            $metaEntity->addProperty(
                (new MetaProperty())
                    ->setName($fieldName)
                    ->setColumnName($joinColumn['name'] ?? null)
                    ->setType(static::getRelationTypeFromAssociationMapping($associationMapping))
                    ->setNullable($joinColumn['nullable'] ?? false)
                    ->setReferencedColumnName($joinColumn['referencedColumnName'] ?? 'id')
                    ->setUnique($joinColumn['unique'] ?? false)
                    ->setMappedBy($associationMapping['mappedBy'] ?? null)
                    ->setInversedBy($associationMapping['inversedBy'] ?? null)
                    ->setOrphanRemoval($associationMapping['orphanRemoval'] ?? null)
            );
        }
    }

    protected static function isTraitField(\ReflectionClass $reflectionClass, $fieldName): bool
    {
        foreach ($reflectionClass->getTraits() as $trait) {
            foreach ($trait->getProperties() as $traitProperty) {
                if ($traitProperty->getName() === $fieldName) {
                    return true;
                }
            }
        }
        return false;
    }

    protected static function getRelationTypeFromAssociationMapping($associationMapping)
    {
        $type = null;
        switch ($associationMapping['type']){
            case 1:
                $type = 'many2many';    //TODO: checken of dit klopt
                break;
            case 2:
                $type = 'many2one';
                break;
            case 3:
                $type = 'one2one';    //TODO: checken of dit klopt
                break;
            case 4:
                $type = 'one2many';
                break;
        }
        return $type;
    }

    protected static function setInterfaces(MetaEntity $metaEntity, \ReflectionClass $reflectionClass)
    {
        foreach ($reflectionClass->getInterfaces() as $interface) {
            $metaEntity->addInterface(
                (new MetaInterface())
                    ->setNamespace($interface->getNamespaceName())
                    ->setName($interface->getShortName())
            );
        }
    }

    protected static function setTraits(MetaEntity $metaEntity, \ReflectionClass $reflectionClass)
    {
        foreach ($reflectionClass->getInterfaces() as $interface) {
            $metaEntity->addInterface(
                (new MetaInterface())
                    ->setNamespace($interface->getNamespaceName())
                    ->setName($interface->getShortName())
            );
        }
    }
}
