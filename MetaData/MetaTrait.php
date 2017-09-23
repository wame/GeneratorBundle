<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\MetaData;

class MetaTrait
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $namespace;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
