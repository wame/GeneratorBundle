<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\MetaData;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Inflector;
use Wame\SensioGeneratorBundle\Twig\InflectorExtension;

class MetaProperty
{
    /** @var MetaEntity */
    protected $entity;

    /** @var string */
    protected $name;

    /** @var string */
    protected $columnName;

    /** @var string */
    protected $type = 'string';

    /** @var bool */
    protected $nullable;

    /** @var bool */
    protected $unique = false;

    /** @var int */
    protected $length;

    /** @var int */
    protected $scale;

    /** @var int */
    protected $precision;

    /** @var bool */
    protected $id = false;

    /** @var string */
    protected $targetEntity;

    /** @var string */
    protected $referencedColumnName = 'id';

    /** @var bool */
    protected $inversedBy = false;

    /** @var bool */
    protected $orphanRemoval = true;

    /** @var MetaValidation[]|ArrayCollection */
    protected $validations = [];

    public function __construct()
    {
        $this->validations = new ArrayCollection();
    }

    /**
     * @return MetaEntity
     */
    public function getEntity(): MetaEntity
    {
        return $this->entity;
    }

    /**
     * @param MetaEntity $entity
     * @return MetaProperty
     */
    public function setEntity($entity): self
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MetaProperty
     */
    public function setName(string $name): MetaProperty
    {
        $this->name = Inflector::camelize($name);
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return MetaProperty
     */
    public function setType(?string $type): MetaProperty
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNullable(): ?bool
    {
        return $this->nullable;
    }

    /**
     * @param bool $nullable
     * @return MetaProperty
     */
    public function setNullable(?bool $nullable): MetaProperty
    {
        $this->nullable = $nullable;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUnique(): ?bool
    {
        return $this->unique;
    }

    /**
     * @param bool $unique
     * @return MetaProperty
     */
    public function setUnique(bool $unique): MetaProperty
    {
        $this->unique = $unique;
        return $this;
    }

    /**
     * @return int
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * @param int $length
     * @return MetaProperty
     */
    public function setLength(?int $length): MetaProperty
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return int
     */
    public function getScale(): ?int
    {
        return $this->scale;
    }

    /**
     * @param int $scale
     * @return MetaProperty
     */
    public function setScale(?int $scale): MetaProperty
    {
        $this->scale = $scale;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    /**
     * @param int $precision
     * @return MetaProperty
     */
    public function setPrecision(?int $precision): MetaProperty
    {
        $this->precision = $precision;
        return $this;
    }

    /**
     * @return bool
     */
    public function isId(): ?bool
    {
        return $this->id;
    }

    /**
     * @param bool $id
     * @return MetaProperty
     */
    public function setId(bool $id): MetaProperty
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTargetEntity(): ?string
    {
        return $this->targetEntity;
    }

    /**
     * @param string $targetEntity
     * @return MetaProperty
     */
    public function setTargetEntity(?string $targetEntity): MetaProperty
    {
        $this->targetEntity = $targetEntity;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInversedBy(): bool
    {
        return $this->inversedBy;
    }

    /**
     * @param bool $inversedBy
     * @return MetaProperty
     */
    public function setInversedBy(?bool $inversedBy): MetaProperty
    {
        $this->inversedBy = $inversedBy;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOrphanRemoval(): bool
    {
        return $this->orphanRemoval;
    }

    /**
     * @param bool $orphanRemoval
     * @return MetaProperty
     */
    public function setOrphanRemoval(?bool $orphanRemoval): MetaProperty
    {
        $this->orphanRemoval = $orphanRemoval;
        return $this;
    }

    public function getColumnName(): ?string
    {
        return $this->columnName ??  Inflector::tableize($this->getName());
    }

    public function setColumnName(?string $columnName): self
    {
        $this->columnName = $columnName;
        return $this;
    }

    public function getReferencedColumnName(): ?string
    {
        return $this->referencedColumnName;
    }

    public function setReferencedColumnName(?string $referencedColumnName): self
    {
        $this->referencedColumnName = $referencedColumnName;
        return $this;
    }

    /** @return ArrayCollection|MetaValidation[] */
    public function getValidations(): ArrayCollection
    {
        return $this->validations;
    }

    public function setValidations(ArrayCollection $validations): self
    {
        $this->validations = $validations;
        return $this;
    }

    public function addValidation(MetaValidation $validations): self
    {
        $this->validations->add($validations);
        return $this;
    }

    public function removeValidation(MetaValidation $validations): self
    {
        $this->validations->removeElement($validations);
        return $this;
    }

    public function isRelationType()
    {
        return in_array($this->getType(), ['many2one', 'many2many', 'one2many', 'one2one'], true);
    }

    public function getTabalizedTargetEntity()
    {
        return Inflector::tableize($this->getTargetEntity());
    }

    public function getReturnType($annotation = false)
    {
        //TODO: check more types. Perhaps also use constants
        switch ($type = $this->getType()) {
            case 'datetime':
            case 'date':
            case 'time':
                return '\DateTime';
            case 'decimal':
                return 'float';
            case 'one2many':
            case 'many2many':
                return 'Collection'. ($annotation ? '|'.$this->getTargetEntity().'[]' : '');
            case 'many2one':
            case 'one2one':
                return $this->getTargetEntity();
            case 'enum':
            case 'text':
                return 'string';
            default:
                return $type;
        }
    }

    public function isCollectionType(): bool
    {
        return $this->getReturnType(false) === 'Collection';
    }
}
