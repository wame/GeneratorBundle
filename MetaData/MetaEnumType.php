<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\MetaData;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class MetaEnumType
{
    /** @var BundleInterface */
    protected $bundle;

    /** @var string */
    protected $className;

    /** @var MetaEnumTypeOption[] */
    protected $options;

    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    /**
     * Creates ArrayCollection of MetaEnumTypes out of array in which the array conforms the following format:
     *      [
     *          [{value}, {constant}, {label}],
     *          ...
     *      ]
     */
    public static function createFromArray(array $enumArrays): self
    {
        $metaEnumType = new MetaEnumType();
        foreach ($enumArrays as $enumArray) {
            $metaEnumType->getOptions()->add(
                (new MetaEnumTypeOption())
                    ->setValue($enumArray[0])
                    ->setConst($enumArray[1])
                    ->setLabel($enumArray[2])
            );
        }
        return $metaEnumType;
    }

    public function addOption(MetaEnumTypeOption $metaEnumTypeOption): self
    {
        $this->options->add($metaEnumTypeOption);
        $metaEnumTypeOption->setMetaEnumType($this);
        return $this;
    }

    public function removeOption(MetaEnumTypeOption $metaEnumTypeOption): self
    {
        $this->options->remove($metaEnumTypeOption);
        $metaEnumTypeOption->setMetaEnumType(null);
        return $this;
    }

    /** @return MetaEnumTypeOption[] */
    public function getOptions(): ArrayCollection
    {
        return $this->options;
    }

    public function getBundle(): ?BundleInterface
    {
        return $this->bundle;
    }

    public function setBundle(?BundleInterface $bundle): self
    {
        $this->bundle = $bundle;
        return $this;
    }

    public function getBundleNamespace(): string
    {
        return $this->getBundle() ? $this->getBundle()->getNamespace() : 'App';
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function setClassName(string $className): self
    {
        $this->className = $className;
        return $this;
    }
}
