<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Command;

use Wame\GeneratorBundle\Inflector\Inflector;

class WameValidators extends Validators
{
    public static function getEnumNameValidator($defaultBundle): callable
    {
        return function ($enum) use ($defaultBundle) {
            if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $enum)) {
                throw new \InvalidArgumentException('The enum name contains invalid characters.');
            }
            if (!preg_match('/Type$/', $enum)) {
                throw new \InvalidArgumentException('The enum name must end with Type.');
            }
            if ($defaultBundle !== null && strpos($enum, ':') === false) {
                $enum = $defaultBundle. ':'. $enum;
            }
            return $enum;
        };
    }

    public static function getEntityNameValidator($defaultBundle): callable
    {
        return function ($name) use ($defaultBundle) {
            if ($defaultBundle !== null && strpos($name, ':') === false) {
                $name = $defaultBundle. ':'. $name;
            }
            return static::validateEntityName($name);
        };
    }

    public static function getFieldNameValidator(array $fields = []): callable
    {
        return function ($name) use ($fields) {
            $name = Inflector::tableize($name);
            if ('id' === $name || isset($fields[$name])) {
                throw new \InvalidArgumentException(sprintf('Field "%s" is already defined.', $name));
            }

            // check reserved words
            if (in_array($name, static::getReservedWords())) {
                throw new \InvalidArgumentException(sprintf('Name "%s" is a reserved word.', $name));
            }

            return $name;
        };
    }

    public static function getTypeValidator(array $types): callable
    {
        return function ($type) use ($types) {
            if (!in_array($type, $types, true)) {
                throw new \InvalidArgumentException(sprintf('Invalid type "%s".', $type));
            }
            return $type;
        };
    }

    public static function getTypeNormalizer(array $types): callable
    {
        return function ($type) use ($types) {
            if (in_array($type, $types, true)) {
                return array_search($type, $types);
            }
            return $type;
        };
    }

    public static function getLengthValidator(): callable
    {
        return function ($length) {
            if (!$length) {
                return $length;
            }

            $result = filter_var($length, FILTER_VALIDATE_INT, array(
                'options' => array('min_range' => 1),
            ));

            if (false === $result) {
                throw new \InvalidArgumentException(sprintf('Invalid length "%s".', $length));
            }

            return $length;
        };
    }

    public static function getBoolValidator(): callable
    {
        return function ($value) {
            if (null === $valueAsBool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
                throw new \InvalidArgumentException(sprintf('Invalid bool value "%s".', $value));
            }

            return $valueAsBool;
        };
    }

    public static function getPrecisionValidator() : callable
    {
        return function ($precision) {
            if (!$precision) {
                return $precision;
            }

            $result = filter_var($precision, FILTER_VALIDATE_INT, array(
                'options' => array('min_range' => 1, 'max_range' => 65),
            ));

            if (false === $result) {
                throw new \InvalidArgumentException(sprintf('Invalid precision "%s".', $precision));
            }

            return $precision;
        };
    }

    public static function getScaleValidator(): callable
    {
        return function ($scale) {
            if (!$scale) {
                return $scale;
            }

            $result = filter_var($scale, FILTER_VALIDATE_INT, array(
                'options' => array('min_range' => 0, 'max_range' => 30),
            ));

            if (false === $result) {
                throw new \InvalidArgumentException(sprintf('Invalid scale "%s".', $scale));
            }

            return $scale;
        };
    }

    //TODO: this isn't actually a validator: should we a different class instead?
    public static function getEntityNormalizer($bundle, $existingEntityOptions): callable
    {
        return function ($entity) use ($bundle, $existingEntityOptions) {
            if (ctype_digit($entity) && isset($existingEntityOptions[$entity])) {
                return $existingEntityOptions[$entity];
            }
            if (strpos($entity, ':') === false) {
                $entity = $bundle . ':' . $entity;
            }
            return $entity;
        };
    }

    public static function getEnumTypeValidator($enumOptionsList): callable
    {
        return function ($type) use ($enumOptionsList) {
            if (!$type) {
                return null;
            }
            if (is_int($type) || ctype_digit($type)) {
                if (!in_array($type, $enumOptionsList)) {
                    throw new \InvalidArgumentException(sprintf('%d is not a valid option', $type));
                }
                if ($type == 0) {
                    return null;
                }
                return array_search($type, $enumOptionsList);
            }
            if (!array_key_exists($type, $enumOptionsList)) {
                throw new \InvalidArgumentException(sprintf("'%s' is not a valid option", $type));
            }
            return $type;
        };
    }

    public static function getConstraintValidator($constraintOptions): callable
    {
        return function ($constraint) use ($constraintOptions) {
            if (!$constraint) {
                return null;
            }
            if (ctype_digit($constraint) && isset($constraintOptions[$constraint])) {
                $constraint = $constraintOptions[$constraint];
            } elseif (!in_array($constraint, $constraintOptions)) {
                throw new \InvalidArgumentException(sprintf(
                    "Unknown validation constraint '%s'! Available options: [%s]",
                    $constraint,
                    implode(', ', $constraintOptions)
                ));
            }
            return $constraint;
        };
    }

    public static function getConstraintsNormalizer(): callable
    {
        return function ($value) {
            if (is_int($value) || ctype_digit($value)) {
                return (int) $value;
            }
            if (is_array($value)) {
                return $value;
            }
            if ($value === 'yes') {
                return true;
            }
            if ($value === 'no') {
                return false;
            }
            try {
                $decodeValue = @json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Could not decode value as json');
                }
                return $decodeValue;
            } catch (\Exception $e) {
                return $value;
            }
        };
    }

    public static function getDisplayFieldValidator($displayFieldOptions): callable
    {
        return function ($field) use ($displayFieldOptions) {
            if (!$field) {
                return null;
            }
            if (ctype_digit($field) && isset($displayFieldOptions[$field])) {
                return $displayFieldOptions[$field];
            }
            if (!in_array($field, $displayFieldOptions, true)) {
                throw new \InvalidArgumentException(sprintf('Invalid field "%s".', $field));
            }

            return $field;
        };
    }

    public static function validateFields(array $fields)
    {
        foreach ($fields as $field) {
            $propertyName = isset($field['fieldName']) ? Inflector::camelize($field['fieldName']) : Inflector::camelize($field['columnName']);

            $type = $field['type'] ?? null;
            $targetEntity = $field['targetEntity'] ?? null;
            $mappedBy = $field['mappedBy'] ?? null;
            $inversedBy = $field['inversedBy'] ?? null;

            if ($targetEntity === null && in_array($type, ['many2one', 'one2one', 'one2many', 'many2many'], true)) {
                throw new \InvalidArgumentException(sprintf('The property \'%s\' is of type \'%s\', but has no targetEntity', $propertyName, $type));
            }

            if ($mappedBy && $inversedBy) {
                throw new \InvalidArgumentException(sprintf('The property \'%s\' has both mappedBy and inversedBy set, which is not a possible combination', $propertyName));
            }
            if ($type === 'one2many' && $inversedBy) {
                throw new \InvalidArgumentException(sprintf('The property \'%s\' is one2many, but has inversedBy set, which is not a possibile combination', $propertyName));
            }
            if ($type === 'many2one' && $mappedBy) {
                throw new \InvalidArgumentException(sprintf('The property \'%s\' is many2one, but has mappedBy set, which is not a possibile combination', $propertyName));
            }
        }
    }
    public static function normalizeFields(array $fields): array
    {
        $newFieldSet = [];
        foreach ($fields as $field) {
            $propertyName = isset($field['fieldName']) ? Inflector::camelize($field['fieldName']) : Inflector::camelize($field['columnName']);
            $columnName = $field['columnName'] ?? Inflector::tableize($propertyName);

            $field['fieldName'] = $propertyName;
            $field['columnName'] = $columnName;

            $type = $field['type'] ?? null;

            if (in_array($type, ['many2one', 'one2one'], true)) {
                if (substr($propertyName, -2) === 'Id') {
                    $field['fieldName'] = str_replace('Id', '', $propertyName);
                }
                if (substr($columnName, -3) !== '_id') {
                    $field['columnName'] = $columnName.'_id';
                }
            }
            $newFieldSet[] = $field;
        }
        return $newFieldSet;
    }
}
