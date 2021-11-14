<?php

namespace DM\LighthouseSchemaGenerator\Support;

class RelationTypes
{
    public const HAS_ONE = 'HasOne';
    public const BELONGS_TO = 'BelongsTo';
    public const HAS_MANY = 'HasMany';
    public const BELONGS_TO_MANY = 'BelongsToMany';
    public const MORPH_ONE = 'MorphOne';
    public const MORPH_TO = 'MorphTo';
    public const MORPH_MANY = 'MorphMany';
    public const MORPH_TO_MANY = 'MorphToMany';

    /** @var string[]  */
    public const RELATION_TYPES = [
        self::HAS_ONE,
        self::BELONGS_TO,
        self::HAS_MANY,
        self::BELONGS_TO_MANY,
        self::MORPH_ONE,
        self::MORPH_TO,
        self::MORPH_MANY,
        self::MORPH_TO_MANY,
    ];

    public const SINGLE_RELATION_TYPES = [
        self::HAS_ONE,
        self::BELONGS_TO,
        self::MORPH_ONE,
        self::MORPH_TO,
    ];

    public const MULTIPLE_RELATION_TYPES = [
        self::HAS_MANY,
        self::BELONGS_TO_MANY,
        self::MORPH_MANY,
        self::MORPH_TO_MANY,
    ];
}