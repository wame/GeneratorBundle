<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\MetaData;

use Doctrine\Common\Collections\ArrayCollection;
use Wame\GeneratorBundle\DBAL\Types\Type;
use \Wame\GeneratorBundle\Inflector\Inflector;

class MetaProperty
{
    /** @var MetaEntity */
    protected $entity;

    /** @var string */
    protected $name;

    /** @var string */
    protected $columnName;

    /** @var string */
    protected $type = Type::STRING;

    /** @var string */
    protected $enumType;

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

    /** @var bool */
    protected $displayField = false;

    /** @var string */
    protected $targetEntity;

    /** @var string */
    protected $referencedColumnName = 'id';

    /** @var string */
    protected $inversedBy;

    /** @var string */
    protected $mappedBy;

    /** @var bool */
    protected $orphanRemoval = true;

    /** @var MetaValidation[]|ArrayCollection */
    protected $validations = [];

    public function __construct()
    {
        $this->validations = new ArrayCollection();
    }

    public function getEntity(): MetaEntity
    {
        return $this->entity;
    }

    public function setEntity(MetaEntity $entity): self
    {
        $this->entity = $entity;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): MetaProperty
    {
        $this->name = Inflector::camelize($name);
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): MetaProperty
    {
        $this->type = $type;
        return $this;
    }

    public function getEnumType(): ?string
    {
        return $this->enumType;
    }

    public function setEnumType(?string $enumType): self
    {
        $this->enumType = $enumType;
        return $this;
    }

    public function isNullable(): ?bool
    {
        return $this->nullable;
    }

    public function setNullable(?bool $nullable): MetaProperty
    {
        $this->nullable = $nullable;
        return $this;
    }

    public function isUnique(): ?bool
    {
        return $this->unique;
    }

    public function setUnique(bool $unique): MetaProperty
    {
        $this->unique = $unique;
        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): MetaProperty
    {
        $this->length = $length;
        return $this;
    }

    public function getScale(): ?int
    {
        return $this->scale;
    }

    public function setScale(?int $scale): MetaProperty
    {
        $this->scale = $scale;
        return $this;
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function setPrecision(?int $precision): MetaProperty
    {
        $this->precision = $precision;
        return $this;
    }

    public function isId(): ?bool
    {
        return $this->id;
    }

    public function setId(bool $id): MetaProperty
    {
        $this->id = $id;
        return $this;
    }

    public function isDisplayField(): ?bool
    {
        return $this->displayField;
    }

    public function setDisplayField(bool $displayField): self
    {
        $this->displayField = $displayField;
        return $this;
    }

    public function getTargetEntity(): ?string
    {
        if (strpos($this->targetEntity, ':') !== false) {
            $targetEntityParts = explode(':', $this->targetEntity);
            return $targetEntityParts[1];
        }
        return $this->targetEntity;
    }

    public function getTargetEntityNamespace(): ?string
    {
        if (strpos($this->targetEntity, ':') !== false) {
            $targetEntityParts = explode(':', $this->targetEntity);
            return $targetEntityParts[0].'\\Entity\\'.$targetEntityParts[1];
        }
        return $this->getEntity()->getBundleNamespace().'\\Entity\\'.$this->targetEntity;
    }

    public function setTargetEntity(?string $targetEntity): MetaProperty
    {
        $this->targetEntity = $targetEntity;
        return $this;
    }

    public function getInversedBy(): ?string
    {
        return $this->inversedBy;
    }

    public function setInversedBy(?string $inversedBy): MetaProperty
    {
        $this->inversedBy = $inversedBy;
        return $this;
    }

    public function getMappedBy(): ?string
    {
        //One2many MUST be mapped by other entity
        if ($this->mappedBy === null && $this->type === Type::ONE2MANY) {
            return Inflector::camelize($this->getEntity()->getEntityName());
        }
        return $this->mappedBy;
    }

    public function setMappedBy(?string $mappedBy): self
    {
        $this->mappedBy = $mappedBy;
        return $this;
    }

    public function isOrphanRemoval(): bool
    {
        //Can only be true for many2many or one2many
        return in_array($this->type, [Type::ONE2MANY, Type::MANY2MANY], true) && $this->orphanRemoval;
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
        return $this->columnName ? $this->columnName : Inflector::tableize($this->getName());
    }

    public function setColumnName(?string $columnName): self
    {
        $this->columnName = Inflector::tableize($columnName);
        return $this;
    }

    public function getReferencedColumnName(): string
    {
        return $this->referencedColumnName;
    }

    public function setReferencedColumnName(string $referencedColumnName): self
    {
        $this->referencedColumnName = Inflector::tableize($referencedColumnName);
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
        return Type::isRelationType($this->getType());
    }

    public function getTabalizedTargetEntity()
    {
        return Inflector::tableize($this->getTargetEntity());
    }

    public function getReturnType($annotation = false)
    {
        switch ($type = $this->getType()) {

            case Type::DATETIME:
            case Type::DATETIMETZ:
            case Type::DATE:
            case Type::TIME:
                return '\DateTime';
            case Type::DATETIME_IMMUTABLE:
            case Type::DATETIMETZ_IMMUTABLE:
            case Type::TIME_IMMUTABLE:
                return '\DateTimeImmutable';
            case Type::DATEINTERVAL:
                return '\DateInterval';
            case Type::ONE2MANY:
            case Type::MANY2MANY:
                return 'Collection'. ($annotation ? '|'.$this->getTargetEntity().'[]' : '');
            case Type::MANY2ONE:
            case Type::ONE2ONE:
                return $this->getTargetEntity();
            case Type::ENUM:
            case Type::TEXT:
            case Type::BLOB:
            case Type::GUID:
            case Type::BINARY:
            case Type::DECIMAL:
            case TYPE::STRING:
            case TYPE::JSON:
                return 'string';
            case Type::INTEGER:
            case Type::SMALLINT:
            case Type::BIGINT:
                return 'int';
            case Type::SIMPLE_ARRAY:
            case Type::JSON_ARRAY:
                return 'array';
            default:
                return $type;
        }
    }

    public function isCollectionType(): bool
    {
        return $this->getReturnType(false) === 'Collection';
    }

    public function isHasValidation(): bool
    {
        return !$this->getValidations()->isEmpty();
    }
}
