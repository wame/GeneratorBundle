<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\DBAL\Types;

abstract class Type extends \Doctrine\DBAL\Types\Type
{
    const MANY2ONE = 'many2one';
    const ONE2MANY = 'one2many';
    const MANY2MANY = 'many2many';
    const ONE2ONE = 'one2one';
    const ENUM = 'enum';

    protected static $aliases = [
        self::ONE2ONE => 'o2o',
        self::MANY2ONE  => 'm2o',
        self::MANY2MANY  => 'm2m',
        self::ONE2MANY  => 'o2m',
        self::ENUM  => 'e',
        //base class types
        self::TARRAY => 'a',
        self::SIMPLE_ARRAY => 'sa',
        self::JSON_ARRAY => 'ja',
        self::JSON => 'j',
        self::BIGINT => 'bi',
        self::BOOLEAN => 'b',
        self::DATETIME => 'dt',
        self::DATETIME_IMMUTABLE => 'dti',
        self::DATETIMETZ => 'dtz',
        self::DATETIMETZ_IMMUTABLE => 'dtzi',
        self::DATE => 'd',
        self::DATE_IMMUTABLE => 'di',
        self::DECIMAL => 'dec',
        self::TIME => 't',
        self::TIME_IMMUTABLE => 'ti',
        self::INTEGER => 'int',
        self::SMALLINT => 'si',
        self::OBJECT => 'o',
        self::STRING => 's',
        self::TEXT => 'x',
        self::FLOAT => 'fl',
        self::BINARY => 'bin',
        self::GUID => 'g',
        self::DATEINTERVAL => 'di'
    ];

    public static function getRelationTypes(): array
    {
        return [static::MANY2MANY, static::MANY2ONE, static::ONE2MANY, static::ONE2ONE];
    }

    public static function isRelationType(string $type): bool
    {
        return in_array($type, static::getRelationTypes(), true);
    }

    public static function getAliases(): array
    {
        return static::$aliases;
    }

    public static function getTypeByAlias($alias): ?string
    {
        if (in_array($alias, static::$aliases, true)) {
            return array_search($alias, static::$aliases);
        }
        return null;
    }
}