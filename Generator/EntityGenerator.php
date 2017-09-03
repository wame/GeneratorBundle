<?php

namespace Wame\SensioGeneratorBundle\Generator;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Doctrine Entity Generator
 *
 * @package Wame\GeneratorBundle
 * @author Ruud Bijnen <ruud@wame.nl>
 */
class EntityGenerator extends \Doctrine\ORM\Tools\EntityGenerator
{
    protected $ignoredPropertyInheritance = [];
    protected $ignoredMethodInheritance = [];

    protected $enumTypes = [];

    protected $traitsToAdd = [];

    protected $classAnnotations = [];

    protected $displayField = null;

    protected $extraProperties = [];

    protected $fieldValidations = [];

    protected $namespaceImports = [
        'Doctrine\ORM\Mapping' => 'ORM',
    ];

    protected $inlineInherited = false;

    /**
     * @var string
     */
    protected static $classTemplate =
        '<?php

<namespace>

<namespaceImports>

<entityAnnotation>
<entityClassName>
{
<entityBody>
}
';

    /**
     * @inheritdoc
     *
     * Note: the original method is copied, as `self::$classTemplate` was used instead of `static::..`
     */
    public function generateEntityClass(ClassMetadataInfo $metadata)
    {
        $placeHolders = [
            '<namespace>',
            '<namespaceImports>',
            '<entityAnnotation>',
            '<entityClassName>',
            '<entityBody>',
            "\r\n",
            "\r",
        ];

        $replacements = [
            $this->generateEntityNamespace($metadata),
            $this->generateEntityNamespaceImports($metadata),
            $this->generateEntityDocBlock($metadata),
            $this->generateEntityClassName($metadata),
            $this->generateEntityBody($metadata),
            "\n",
            "\n",
        ];

        $code = str_replace($placeHolders, $replacements, static::$classTemplate);

        // make sure we don't have trailing whitespace
        $code = implode("\n", array_map('rtrim', explode("\n", $code)));

        return str_replace('<spaces>', $this->spaces, $code);
    }

    protected function generateEntityBody(ClassMetadataInfo $metadata)
    {
        $body = "";
        $body .= $this->generateEntityTraitUseStatements($metadata);
        $body .= $this->generateEntityExtraProperties($metadata);
        $body .= parent::generateEntityBody($metadata);
        $body .= $this->generateEntityToStringMethod($metadata);
        return $body;
    }

    protected function generateEntityDocBlock(ClassMetadataInfo $metadata)
    {
        $docBlock = parent::generateEntityDocBlock($metadata);
        $lines = explode("\n", $docBlock);
        $extraLines = $this->generateEntityAnnotations($metadata);
        array_splice($lines, count($lines) - 1, 0, $extraLines);

        return implode("\n", $lines);
    }

    protected function generateEntityTraitUseStatements(ClassMetadataInfo $metadata)
    {
        $lines = [];
        foreach (array_unique($this->traitsToAdd) as $trait) {
            $lines[] = $this->spaces . 'use ' . $trait . ';';
        }
        if (count($lines) > 0) {
            $lines[] = '';
        }
        return implode("\n", $lines);
    }

    protected function generateEntityToStringMethod(ClassMetadataInfo $metadata)
    {
        if (!$this->displayField) return null;

        if (!$metadata->hasField($this->displayField)) {
            throw new \RuntimeException(sprintf('Display field "%s" not found in %s', $this->displayField, $metadata->getName()));
        }

        $fieldName = $this->displayField;

        $replacements = [
            '<description>'       => 'Returns a string representation of this object; useful for choice lists etc.',
            '<methodTypeHint>'    => 'string',
            '<variableType>'      => 'string',
            '<methodName>'        => '__toString',
            '<fieldName>'         => $fieldName,
            '<entity>'            => $this->getClassName($metadata)
        ];

        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            static::$getMethodTemplate
        );

        return "\n\n".$this->prefixCodeWithSpaces($method);
    }

    protected function generateEntityAnnotations(ClassMetadataInfo $metadata)
    {
        return array_map(function($annotation) { return ' * ' . $annotation; }, $this->classAnnotations);
    }

    protected function generateEntityNamespaceImports(ClassMetadataInfo $metadata)
    {
        $lines = [];

        ksort($this->namespaceImports);

        foreach ($this->namespaceImports as $class => $alias) {
            if (is_int($class)) {
                $class = $alias;
                $alias = null;
            }
            $class = ltrim($class, '\\');
            $lines[] = 'use ' . $class . ($alias ? ' as ' . $alias : '') . ';';
        }
        return implode("\n", $lines);
    }

    protected function generateFieldMappingPropertyDocBlock(array $fieldMapping, ClassMetadataInfo $metadata)
    {
        $docBlock =  parent::generateFieldMappingPropertyDocBlock(
            $fieldMapping,
            $metadata
        );

        $lines = explode("\n", $docBlock);
        $extraLines = $this->generateFieldMappingPropertyValidation($fieldMapping, $metadata);
        array_splice($lines, count($lines) - 1, 0, $extraLines);

        return implode("\n", $lines);
    }

    protected function generateFieldMappingPropertyValidation($fieldMapping, $metadata)
    {
        $lines = [];

        if ($this->isEnumType($fieldMapping)) {
            $enumClass = $this->getEnumTypeClass($fieldMapping);
            $lines[] = $this->spaces . sprintf(' * @EnumAssert\\Enum(entity="%s")', ltrim($enumClass, '\\'));
        }
        if (isset($this->fieldValidations[$fieldMapping['fieldName']])) {
            $lines[] = $this->generatePropertyValidation($this->fieldValidations[$fieldMapping['fieldName']]);
        }

        return $lines;
    }

    protected function generateEntityExtraProperties(ClassMetadataInfo $metadata)
    {
        $lines = [];

        foreach ($this->extraProperties as $propertyOptions) {
            if ($this->hasProperty($propertyOptions['propertyName'], $metadata) ||
                $metadata->hasField($propertyOptions['propertyName'])) {
                continue;
            }

            $lines[] = $this->spaces . '/**';
            $lines[] = $this->spaces . ' * @var ' . (isset($propertyOptions['type']) ? $propertyOptions['type'] : 'mixed');

            if (!empty($propertyOptions['comment'])) {
                $lines[] = $this->spaces . ' *';
                foreach (explode("\n", $propertyOptions['comment']) as $line) {
                    $lines[] = $this->spaces . ' * ' . rtrim($line);
                }
            }

            if (!empty($propertyOptions['validation'])) {
                $lines[] = $this->spaces . ' *';
                $lines[] = $this->generatePropertyValidation($propertyOptions['validation']);
            }

            $lines[] = $this->spaces . ' */';

            $lines[] = $this->spaces . $this->fieldVisibility . ' $' . $propertyOptions['propertyName']
                . (isset($propertyOptions['default']) ? ' = ' . var_export($propertyOptions['default'], true) : null) . ";\n";
        }

        if (count($lines) > 0) {
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    protected function generatePropertyValidation($validations)
    {
        $lines = [];
        foreach ($validations as $validation) {
            if (is_string($validation)) {
                $lines[] = $this->spaces . ' * @' . $validation;
            } else {
                $type = $validation['type'];
                $options = isset($validation['options']) ? $validation['options'] : null;
                $prefix = array_key_exists('prefix', $validation) ? $validation['prefix'] : null;
                $lines[] = $this->spaces . ' * ' . $this->generateValidationAnnotation($type, $options, $prefix);
            }
        }
        return implode("\n", $lines);
    }

    protected function generateAnnotation($type, $options = null, $prefix = null)
    {
        return '@' . $prefix . $type . '(' . ($options !== null ? $this->generateAnnotationValue($options) : '')  . ')';
    }

    protected function generateValidationAnnotation($type, $options = null, $prefix = null)
    {
        if ($prefix === null) {
            $prefix = 'Assert\\';
        }
        return $this->generateAnnotation($type, $options, $prefix);
    }

    protected function generateAnnotationValue($value, $depth = 0)
    {
        if (is_array($value)) {
            $assoc = (bool)count(array_filter(array_keys($value), 'is_string'));
            $out = '';
            if ($depth > 0) $out .= '{';
            $elements = [];
            foreach ($value as $key => $val) {
                $elements[] = ($assoc ? $key . '=' : '') . $this->generateAnnotationValue($val, $depth + 1);
            }
            $out .= implode(', ', $elements);
            if ($depth > 0) $out .= '}';
            return $out;
        } elseif (is_string($value)) {
            return '"' . str_replace('"', '\"', $value) . '"';
        }
        return var_export($value, true);
    }


    public function ignorePropertyInheritance($property, $ignore = true)
    {
        $this->ignoredPropertyInheritance[$property] = $ignore;
    }

    public function ignoreMethodInheritance($method, $ignore = true)
    {
        $this->ignoredMethodInheritance[$method] = $ignore;
    }

    /**
     * @return array
     */
    public function getEnumTypes()
    {
        return $this->enumTypes;
    }

    /**
     * @param array $enumTypes
     */
    public function setEnumTypes(array $enumTypes)
    {
        foreach ($enumTypes as $type => $class) {
            $this->setEnumType($type, $class);
        }
    }

    /**
     * @param string $type
     * @param string $class
     */
    public function setEnumType($type, $class)
    {
        $this->enumTypes[$type] = $class;
        $this->typeAlias[$type] = 'string';

        // Make sure this annotation namespace gets imported
        $this->addNamespaceImport('Fresh\DoctrineEnumBundle\Validator\Constraints', 'EnumAssert');
    }

    protected function isEnumType(array $fieldMapping)
    {
        return (isset($fieldMapping['type']) && isset($this->enumTypes[$fieldMapping['type']]));
    }

    protected function getEnumTypeClass(array $fieldMapping)
    {
        return $this->enumTypes[$fieldMapping['type']];
    }

    protected function addTypeAlias($type, $alias)
    {
        $this->typeAlias[$type] = $alias;
    }

    /**
     * @return array
     */
    public function getTraitsToAdd()
    {
        return $this->traitsToAdd;
    }

    /**
     * @param string $trait
     */
    public function addTrait($trait)
    {
        $this->traitsToAdd[] = $trait;
    }

    /**
     * @param array $traitsToAdd
     */
    public function setTraitsToAdd($traitsToAdd)
    {
        $this->traitsToAdd = $traitsToAdd;
    }

    /**
     * @return array
     */
    public function getExtraProperties()
    {
        return $this->extraProperties;
    }

    /**
     * @param array $property
     */
    public function addExtraProperty(array $property)
    {
        if (!isset($property['propertyName'])) {
            throw new \InvalidArgumentException("Passed property not an array or 'propertyName' element is missing.");
        }
        $this->extraProperties[$property['propertyName']] = $property;
    }

    /**
     * @param array $properties
     */
    public function setExtraProperties($properties)
    {
        $this->extraProperties = $properties;
    }


    /**
     * @param string|null $displayField
     */
    public function setDisplayField($displayField)
    {
        $this->displayField = $displayField;
    }

    public function addClassAnnotation($annotation)
    {
        $this->classAnnotations[] = $annotation;
    }

    public function addNamespaceImport($class, $alias = null)
    {
        $this->namespaceImports[$class] = $alias;
    }

    /**
     * @param string $fieldName
     * @param array|string $validation
     */
    public function addFieldValidation($fieldName, $validation)
    {
        if (!isset($this->fieldValidations[$fieldName])) {
            $this->fieldValidations[$fieldName] = [];
        }
        $this->fieldValidations[$fieldName][] = $validation;
    }

    /**
     * @param string $fieldName
     * @param array $validations
     */
    public function setFieldValidation($fieldName, $validations)
    {
        $this->fieldValidations[$fieldName] = $validations;
    }

    /**
     * @param string $fieldName
     * @return array
     */
    public function getFieldValidation($fieldName)
    {
        if (!isset($this->fieldValidations[$fieldName])) {
            return [];
        }
        return $this->fieldValidations[$fieldName];
    }

    public function setFieldValidations(array $fieldValidations)
    {
        $this->fieldValidations = $fieldValidations;
    }

    public function resetFieldValidations()
    {
        $this->fieldValidations = [];
    }

    /**
     * @param boolean $inlineInherited
     * @return EntityGenerator
     */
    public function setInlineInherited($inlineInherited)
    {
        $this->inlineInherited = $inlineInherited;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function hasProperty($property, ClassMetadataInfo $metadata)
    {
        if ($this->extendsClass() && !isset($this->ignoredPropertyInheritance[$property])) {
            // don't generate property if its already on the base class.
            $reflClass = new \ReflectionClass($this->getClassToExtend());
            if ($reflClass->hasProperty($property)) {
                return true;
            }
        }

        return (
            isset($this->staticReflection[$metadata->name]) &&
            in_array($property, $this->staticReflection[$metadata->name]['properties'])
        );
    }

    /**
     * @inheritdoc
     */
    protected function hasMethod($method, ClassMetadataInfo $metadata)
    {
        if (
            isset($this->ignoredMethodInheritance[$method]) &&
            isset($this->staticReflection[$metadata->name]) &&
            !in_array(strtolower($method), $this->staticReflection[$metadata->name]['methods'])
        ) {
            return false;
        }

        return parent::hasMethod($method, $metadata);
    }

    protected function parseTokensInEntityFile($src)
    {
        parent::parseTokensInEntityFile($src);

        if ($this->inlineInherited) {
            return;
        }

        $tokens = token_get_all($src);
        $lastSeenNamespace = "";
        $lastSeenClass = false;

        $inNamespace = false;
        $inClass = false;

        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];
            if (in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT))) {
                continue;
            }

            if ($inNamespace) {
                if ($token[0] == T_NS_SEPARATOR || $token[0] == T_STRING) {
                    $lastSeenNamespace .= $token[1];
                } elseif (is_string($token) && in_array($token, array(';', '{'))) {
                    $inNamespace = false;
                }
            }

            if ($inClass) {
                $inClass = false;
                $lastSeenClass = $lastSeenNamespace . ($lastSeenNamespace ? '\\' : '') . $token[1];
                $ref = new \ReflectionClass($lastSeenClass);
                foreach ($ref->getMethods() as $methodRef) {
                    if (!in_array($methodRef->getName(), $this->staticReflection[$lastSeenClass]['methods'])) {
                        $this->staticReflection[$lastSeenClass]['methods'][] = $methodRef->getName();
                    }
                }
                foreach ($ref->getProperties() as $propertyRef) {
                    if (!in_array($propertyRef->getName(), $this->staticReflection[$lastSeenClass]['properties'])) {
                        $this->staticReflection[$lastSeenClass]['properties'][] = $propertyRef->getName();
                    }
                }
            }

            if ($token[0] == T_NAMESPACE) {
                $lastSeenNamespace = "";
                $inNamespace = true;
            } elseif ($token[0] == T_CLASS) {
                $inClass = true;
            }
        }
    }
}
