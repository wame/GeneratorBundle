<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\MetaData;

class MetaValidation
{
    /** @var string */
    protected $type;

    /** @var array */
    protected $options;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function __toString()
    {
        return $this->getType();
    }
}
