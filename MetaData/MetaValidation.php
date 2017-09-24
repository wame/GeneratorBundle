<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\MetaData;

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

    public function getAnnotationFormatted(): string
    {
        $formattedString = $this->getType();
        $numberOfOptions = count($this->getOptions());
        if ($numberOfOptions === 0) {
            return $formattedString;
        }
        $formattedString .= "(";
        $first = true;
        foreach ($this->getOptions() as $optionName => $optionValue) {
            if ($first === false) {
                $formattedString .= ', ';
            }
            $first = false;
            if ($numberOfOptions > 1) {
                $formattedString .= $optionName.'=';
            }
            if (is_array($optionValue)) {
                $formattedString .= $this->arrayContainsIntOnly($optionValue)
                    ? implode(', ', $optionValue)
                    : ('"'.implode('", "', $optionValue) ).'"';
            } else {
                $formattedString .= is_int($optionValue) ? $optionValue : ('"'.$optionValue.'"');
            }
        }
        $formattedString .= ")";

        return $formattedString;
    }

    protected function arrayContainsIntOnly(array $values): bool
    {
        foreach ($values as $value) {
            if (is_int($value) === false) {
                return false;
            }
        }
        return true;
    }

    public function __toString()
    {
        return $this->getType();
    }
}
