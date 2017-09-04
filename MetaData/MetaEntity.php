<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\MetaData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;

class MetaEntity
{
    /** @var string */
    protected $entityName;

    /** @var string */
    protected $tableName;

    /** @var string */
    protected $bundleNamespace;

    /** @var MetaInterface[]|ArrayCollection */
    protected $interfaces;

    /** @var MetaTrait[]|ArrayCollection */
    protected $traits;

    /** @var string */
    protected $constructor;

    /** @var MetaProperty[]|ArrayCollection */
    protected $properties;

    public function __construct()
    {
        $this->properties = new ArrayCollection();
        $this->interfaces = new ArrayCollection();
        $this->traits = new ArrayCollection();
    }

    public static function createFromClassMetadata(ClassMetadata $classMetadata)
    {
        $reflectionClass = $classMetadata->getReflectionClass();
        $entityMetadata = (new self())
            ->setBundleNamespace($classMetadata->namespace)
            ->setEntityName($reflectionClass->getShortName())
            ->setTableName($classMetadata->getTableName())
        ;
        foreach ($reflectionClass->getInterfaces() as $interface) {
            $entityMetadata->addInterface(
                (new MetaInterface())
                    ->setNamespace($interface->getNamespaceName())
                    ->setName($interface->getShortName())
            );
        }
        foreach ($reflectionClass->getTraits() as $trait) {
            $entityMetadata->addTrait(
                (new MetaTrait())
                    ->setName($trait->getShortName())
                    ->setNamespace($trait->getNamespaceName())
            );
        }

        foreach ($classMetadata->fieldMappings as $fieldName => $fieldMapping) {
            foreach ($reflectionClass->getTraits() as $trait) {
                foreach ($trait->getProperties() as $traitProperty) {
                    if ($traitProperty->getName() === $fieldName) {
                        continue 3;
                    }
                }
            }
            $entityMetadata->addProperty(
                (new MetaProperty())
                    ->setEntity($entityMetadata)
                    ->setId($fieldMapping['id'] ?? false)
                    ->setName($fieldName)
                    ->setType($fieldMapping['type'] ?? null)
                    ->setNullable($fieldMapping['nullable'] ?? false)
                    ->setLength(isset($fieldMapping['length']) ? (int) $fieldMapping['length'] : null)
                    ->setScale(isset($fieldMapping['scale']) ? (int) $fieldMapping['scale'] : null)
                    ->setPrecision(isset($fieldMapping['precision']) ? (int) $fieldMapping['precision'] : null)
            );
        }

        return $entityMetadata;
    }

    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): self
    {
        $this->entityName = $entityName;
        return $this;
    }

    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function getBundleNamespace(): ?string
    {
        return $this->bundleNamespace;
    }

    public function setBundleNamespace(string $bundleNamespace): self
    {
        $this->bundleNamespace = $bundleNamespace;
        return $this;
    }

    /**
     * @return ArrayCollection|MetaTrait[]
     */
    public function getTraits(): ArrayCollection
    {
        return $this->traits;
    }

    public function setTraits(?array $traits): self
    {
        $this->traits = $traits;
        return $this;
    }

    public function addTrait(MetaTrait $trait): self
    {
        $this->traits->add($trait);
        return $this;
    }

    public function removeTrait(MetaTrait $trait): self
    {
        $this->traits->removeElement($trait);
        return $this;
    }

    public function getConstructor(): ?string
    {
        return $this->constructor;
    }

    public function setConstructor(string $constructor): self
    {
        $this->constructor = $constructor;
        return $this;
    }

    /**
     * @return ArrayCollection|MetaProperty[]
     */
    public function getProperties(): ArrayCollection
    {
        return $this->properties;
    }

    public function setProperties($properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    public function addProperty(MetaProperty $property): self
    {
        $this->properties->add($property);
        $property->setEntity($this);
        return $this;
    }

    public function removeProperty(MetaProperty $property): self
    {
        $this->properties->removeElement($property);
        return $this;
    }

    /**
     * @return ArrayCollection|MetaInterface[]
     */
    public function getInterfaces() : ArrayCollection
    {
        return $this->interfaces;
    }

    public function setInterfaces($interfaces): self
    {
        $this->interfaces = $interfaces;
        return $this;
    }

    public function addInterface(MetaInterface $interfaces): self
    {
        $this->interfaces->add($interfaces);
        return $this;
    }

    public function removeInterface(MetaInterface $interfaces): self
    {
        $this->interfaces->removeElement($interfaces);
        return $this;
    }

    public function hasTrait($traitName)
    {
        foreach ($this->getTraits() as $trait) {
            if ($trait->getName() === $traitName) {
                return true;
            }
        }
        return false;
    }
}
