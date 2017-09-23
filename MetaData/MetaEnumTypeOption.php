<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\MetaData;

class MetaEnumTypeOption
{
    /** @var MetaEnumType */
    protected $metaEnumType;

    /** @var string */
    protected $const;

    /** @var string */
    protected $value;

    /** @var string */
    protected $label;

    public function getMetaEnumType(): ?MetaEnumType
    {
        return $this->metaEnumType;
    }

    public function setMetaEnumType(?MetaEnumType $metaEnumType): self
    {
        $this->metaEnumType = $metaEnumType;
        return $this;
    }

    public function getConst(): ?string
    {
        return $this->const;
    }

    public function setConst(string $const): self
    {
        $this->const = $const;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getLabel() ?: '';
    }
}
