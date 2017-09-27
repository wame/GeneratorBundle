<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\MetaData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Wame\GeneratorBundle\Inflector\Inflector;

class MetaEntity
{
    /** @var string */
    protected $entityName;

    /** @var string */
    protected $tableName;

    /** @var BundleInterface */
    protected $bundle;

    /** @var MetaInterface[]|ArrayCollection */
    protected $interfaces;

    /** @var MetaTrait[]|ArrayCollection */
    protected $traits;

    /** @var string */
    protected $constructor;

    /** @var MetaProperty[]|ArrayCollection */
    protected $properties;

    public function __construct(BundleInterface $bundle, string $entityName)
    {
        $this->bundle = $bundle;
        $this->setEntityName($entityName);
        $this->setTableName($entityName);
        $this->properties = new ArrayCollection();
        $this->interfaces = new ArrayCollection();
        $this->traits = new ArrayCollection();
    }

    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): self
    {
        $this->entityName = Inflector::classify($entityName);
        return $this;
    }

    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName): self
    {
        $this->tableName = Inflector::pluralTableize($tableName);
        return $this;
    }

    public function getBundle(): BundleInterface
    {
        return $this->bundle;
    }

    public function getBundleNamespace(): ?string
    {
        return $this->getBundle() ? $this->getBundle()->getNamespace() : null;
    }

    public function setBundle(BundleInterface $bundle): self
    {
        $this->bundle = $bundle;
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

    public function hasTrait($traitName): bool
    {
        foreach ($this->getTraits() as $trait) {
            if (strtolower($trait->getName()) === strtolower($traitName)) {
                return true;
            }
        }
        return false;
    }

    public function isHasCollectionProperties(): bool
    {
        return $this->getCollectionProperties()->isEmpty() === false;
    }

    public function getCollectionProperties(): Collection
    {
        return $this->getProperties()->filter(function (MetaProperty $property) {
            return $property->isCollectionType();
        });
    }

    public function isHasValidation(): bool
    {
        foreach ($this->getProperties() as $property) {
            if ($property->isHasValidation()) {
                return true;
            }
        }
        return false;
    }

    public function isHasEnumProperty(): bool
    {
        foreach ($this->getProperties() as $property) {
            if ($property->getEnumType()) {
                return true;
            }
        }
        return false;
    }

    public function getIdProperty(): ?MetaProperty
    {
        foreach ($this->getProperties() as $property) {
            if ($property->isId()) {
                return $property;
            }
        }
        return null;
    }

    public function getDisplayFieldProperty(): ?MetaProperty
    {
        foreach ($this->getProperties() as $property) {
            if ($property->isDisplayField()) {
                return $property;
            }
        }
        return null;
    }
}
