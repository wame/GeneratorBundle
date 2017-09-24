<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Command\Helper;

use Wame\GeneratorBundle\DBAL\Types\Type;

/**
 * Helper class for Validation Constraints
 *
 * for more info about validation constraints, see:
 * https://symfony.com/doc/current/reference/constraints.html
 *
 * @package Wame\GeneratorBundle\Command
 */
class ValidationConstraints
{
    const BIC = 'Bic';
    const BLANK = 'Blank';
    const CHOICE = 'Choice';
    const COUNTRY = 'Country';
    const CURRENCY = 'Currency';
    const DATE = 'Date';
    const DATETIME = 'DateTime';
    const EMAIL = 'Email';
    const FILE = 'File';
    const IBAN = 'Iban';
    const IMAGE = 'Image';
    const IP = 'Ip';
    const IS_FALSE = 'IsFalse';
    const IS_NULL = 'IsNull';
    const IS_TRUE = 'IsTrue';
    const ISBN = 'Isbn';
    const ISSN = 'Issn';
    const LANGUAGE = 'Language';
    const LOCALE = 'Locale';
    const LUHN = 'Luhn';
    const NOT_BLANK = 'NotBlank';
    const NOT_NULL = 'NotNull';
    const TIME = 'Time';
    const URL = 'Url';
    const UUID = 'Uuid';
    const VALID = 'Valid';
    const LENGTH = 'Length';
    const REGEX = 'Regex';
    const COUNT = 'Count';
    const CARD_SCHEME = 'CardScheme';
    const EXPRESSION = 'Expression';
    const USER_PASSWORD = 'UserPassword';

    const GREATER_THAN_OR_EQUAL = 'GreaterThanOrEqual';
    const GREATER_THAN = 'GreaterThan';
    const LESS_THAN_OR_EQUAL = 'LessThanOrEqual';
    const LESS_THAN = 'LessThan';
    const EQUAL_TO = 'EqualTo';
    const NOT_EQUAL_TO = 'NotEqualTo';
    const IDENTICAL_TO = 'IdenticalTo';
    const NOT_IDENTICAL_TO = 'NotIdenticalTo';

    const RANGE = 'Range';

    protected static $aliases = [
        self::BIC => null,
        self::BLANK => null,
        self::CHOICE => 'c',
        self::COUNTRY => null,
        self::CURRENCY => 'cur',
        self::DATE => 'd',
        self::DATETIME => 'dt',
        self::EMAIL => 'e',
        self::FILE => 'f',
        self::IBAN => null,
        self::IMAGE => 'img',
        self::IP => null,
        self::IS_FALSE => 'false',
        self::IS_NULL => 'null',
        self::IS_TRUE => 'true',
        self::ISBN => null,
        self::ISSN => null,
        self::LANGUAGE => 'lang',
        self::LOCALE => null,
        self::LUHN => null,
        self::NOT_BLANK => 'nb',
        self::NOT_NULL => 'nn',
        self::TIME => 't',
        self::URL => null,
        self::UUID => null,
        self::VALID => 'v',
        self::LENGTH => 'l',
        self::REGEX => 'reg',
        self::COUNT => null,
        self::CARD_SCHEME => 'card',
        self::EXPRESSION => 'expr',
        self::USER_PASSWORD => 'pass',
        self::GREATER_THAN_OR_EQUAL => 'gte',
        self::GREATER_THAN => 'gt',
        self::LESS_THAN_OR_EQUAL => 'lte',
        self::LESS_THAN => 'lt',
        self::EQUAL_TO => 'eq',
        self::NOT_EQUAL_TO => 'neq',
        self::IDENTICAL_TO => 'identical',
        self::NOT_IDENTICAL_TO => 'ni',
        self::RANGE => 'r',
    ];

    protected static $constraintOptions = [
        self::LENGTH => ['max' => 'int', 'min' => 'int'],
        self::COUNT => ['max' => 'int', 'min' => 'int'],
        self::RANGE => ['max' => 'int', 'min' => 'int'],
        self::CHOICE => ['choices' => 'array'],
        self::REGEX => ['pattern' => 'string'],
        self::LESS_THAN => ['value' => 'string'],
        self::LESS_THAN_OR_EQUAL => ['value' => 'string'],
        self::GREATER_THAN => ['value' => 'string'],
        self::GREATER_THAN_OR_EQUAL => ['value' => 'string'],
        self::IDENTICAL_TO => ['value' => 'string'],
        self::NOT_IDENTICAL_TO => ['value' => 'string'],
        self::EQUAL_TO => ['value' => 'string'],
        self::NOT_EQUAL_TO => ['value' => 'string'],
        self::EXPRESSION => ['expression' => 'string'],
        self::CARD_SCHEME => ['schemes' => 'array'],
    ];

    protected static $stringConstraints = [
        self::EMAIL,
        self::LENGTH,
        self::URL,
        self::REGEX,
        self::IP,
        self::UUID,
        self::FILE,
        self::IMAGE,
        self::LANGUAGE,
        self::LOCALE,
        self::COUNTRY,
    ];
    protected static $comparisonConstraints = [
        self::EQUAL_TO,
        self::NOT_EQUAL_TO,
        self::IDENTICAL_TO,
        self::NOT_IDENTICAL_TO,
        self::LESS_THAN,
        self::LESS_THAN_OR_EQUAL,
        self::GREATER_THAN,
        self::GREATER_THAN_OR_EQUAL,
    ];

    protected static $otherNumberConstraints = [
        self::BIC,
        self::CARD_SCHEME,
        self::CURRENCY,
        self::LUHN,
        self::IBAN,
        self::ISBN,
        self::ISSN,
    ];

    public static function getOptionsForConstraint(string $constraint): array
    {
        if (array_key_exists($constraint, static::$constraintOptions)) {
            return static::$constraintOptions[$constraint];
        }
        return [];
    }

    public static function getAliases(): array
    {
        return static::$aliases;
    }

    public static function getAliasesForSet(array $set): array
    {
        $returnAliases = [];
        foreach ($set as $constraint) {
            if (array_key_exists($constraint, static::$aliases) && static::$aliases[$constraint] !== null) {
                $returnAliases[$constraint] = static::$aliases[$constraint];
            }
        }
        return $returnAliases;
    }

    public static function getConstraintSetForType (string $type)
    {
        switch ($type) {
            case Type::DATETIME:
            case Type::DATETIMETZ:
            case Type::DATETIME_IMMUTABLE:
            case Type::DATETIMETZ_IMMUTABLE:
                return array_merge([static::NOT_NULL, static::DATETIME, static::EXPRESSION], static::$comparisonConstraints);
            case Type::DATE:
                return array_merge([static::NOT_NULL, static::DATE, static::EXPRESSION], static::$comparisonConstraints);
            case Type::TIME:
            case Type::TIME_IMMUTABLE:
                return array_merge([static::NOT_NULL, static::TIME, static::EXPRESSION], static::$comparisonConstraints);
            case Type::DATEINTERVAL:
                return array_merge([static::NOT_NULL, static::EXPRESSION], static::$comparisonConstraints);
            case Type::ONE2MANY:
            case Type::MANY2MANY:
                return [static::VALID, static::EXPRESSION];
            case Type::MANY2ONE:
            case Type::ONE2ONE:
                return [static::NOT_NULL, static::VALID, static::EXPRESSION];
            case Type::ENUM:
                return [static::NOT_NULL, static::EXPRESSION];
            case TYPE::STRING:
            case TYPE::JSON:
                return array_merge(
                    [static::NOT_NULL, static::NOT_BLANK, static::USER_PASSWORD, static::EXPRESSION, self::CHOICE],
                    static::$stringConstraints,
                    static::$otherNumberConstraints,
                    static::$comparisonConstraints
                );
            case Type::TEXT:
            case Type::BLOB:
            case Type::BINARY:
                return [static::NOT_NULL, static::NOT_BLANK, static::REGEX, static::EXPRESSION];
            case Type::GUID:
                return [static::NOT_NULL, static::EXPRESSION, static::UUID];
            case Type::DECIMAL:
            case Type::INTEGER:
            case Type::SMALLINT:
            case Type::BIGINT:
                return array_merge([static::NOT_NULL, static::EXPRESSION, static::RANGE], static::$comparisonConstraints);
            case Type::SIMPLE_ARRAY:
            case Type::JSON_ARRAY:
                return array_merge([static::NOT_NULL, static::EXPRESSION, static::COUNT]);
            default:
                return [];
        }
    }

    public static function getDefaultValidationsForType(string $type, bool $nullable = false)
    {
        $notNullValidation = $nullable ? [] : [static::NOT_NULL];
        switch ($type) {
            case Type::DATETIME:
            case Type::DATETIMETZ:
            case Type::DATETIME_IMMUTABLE:
            case Type::DATETIMETZ_IMMUTABLE:
                return array_merge($notNullValidation, [static::DATETIME]);
            case Type::DATE:
                return array_merge($notNullValidation, [static::DATE]);
            case Type::TIME:
            case Type::TIME_IMMUTABLE:
                return array_merge($notNullValidation, [static::TIME]);
            case Type::ONE2MANY:
            case Type::MANY2MANY:
            case Type::MANY2ONE:
            case Type::ONE2ONE:
                return array_merge($notNullValidation, [static::VALID]);
            case Type::ENUM:
                //EnumAssert depends on the 'DoctrineEnumBundle'. It will be added in the twig-file.
                return array_merge($notNullValidation);
            case TYPE::STRING:
            case TYPE::JSON:
            case Type::TEXT:
            case Type::BLOB:
            case Type::BINARY:
            case Type::GUID:
            case Type::DECIMAL:
                return $nullable ? [] : [static::NOT_BLANK];
            default:
                return $notNullValidation;
        }
    }

    public static function getConstraintChoicesForType(string $type, array $blacklist)
    {
        $constraints = ValidationConstraints::getConstraintSetForType($type);
        $constraintChoices = array_diff($constraints, $blacklist);
        $aliases = ValidationConstraints::getAliasesForSet($constraintChoices);
        $constraintChoices = array_combine($constraintChoices, array_fill(0, count($constraintChoices), null));
        return array_merge($constraintChoices, $aliases);
    }
}