<?php

namespace Zerotoprod\DataModelAdapterJsonSchema4;

use Zerotoprod\DataModelAdapterJsonSchema4\Resolvers\PropertyTypeResolver;
use Zerotoprod\DataModelGenerator\Models\BackedEnumType;
use Zerotoprod\DataModelGenerator\Models\Components;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\Constant;
use Zerotoprod\DataModelGenerator\Models\Enum;
use Zerotoprod\DataModelGenerator\Models\EnumCase;
use Zerotoprod\DataModelGenerator\Models\Model;
use Zerotoprod\DataModelGenerator\Models\Property;
use Zerotoprod\JsonSchema4\JsonSchema4;
use Zerotoprod\Psr4Classname\Classname;
use Zerotoprod\Psr4VarName\VarName;

class JsonSchema4
{

    public static function adapt(array $json_schema4, Config $Config): Components
    {
        $JsonSchema4 = JsonSchema4::from($json_schema4);
        $Models = [];
        $Enums = [];

        ['models' => $models, 'enums' => $enums] = self::renderModel($JsonSchema4, $Config, $JsonSchema4->title);
        $Models[] = $models;
        $Enums[] = $enums;
        foreach ($JsonSchema4->properties as $key => $Schema) {
            if (($Schema->type === 'object' || $Schema->type === ['object']) && !$Schema->additionalProperties) {
                ['models' => $models, 'enums' => $enums] = self::renderModel($Schema, $Config, $key);
                $Models[] = $models;
                $Enums[] = $enums;
            }
            if ($Schema->items?->type === 'object') {
                ['models' => $models, 'enums' => $enums] = self::renderModel($Schema->items, $Config, $key, true);
                $Models[] = $models;
                $Enums[] = $enums;
            }
            foreach ($Schema->properties as $property_name => $PropertySchema) {
                if ($PropertySchema->type === 'array' && $PropertySchema->items?->type === 'object' && count($PropertySchema->items->properties)) {
                    ['models' => $models, 'enums' => $enums] = self::renderModel($PropertySchema->items, $Config, $property_name, array: true);
                    $Models[] = $models;
                    $Enums[] = $enums;
                }
            }
        }

        foreach ($JsonSchema4->definitions as $key => $Schema) {
            if ($Schema->items?->type === 'object') {
                ['models' => $models, 'enums' => $enums] = self::renderModel($Schema->items, $Config, $key, true);
                $Models[] = $models;
                $Enums[] = $enums;
            }
            if ($Schema?->type === 'object') {
                ['models' => $models, 'enums' => $enums] = self::renderModel($Schema, $Config, $key);
                $Models[] = $models;
                $Enums[] = $enums;
            }
        }

        return Components::from([
            Components::Config => $Config,
            Components::Models => $Models,
            Components::Enums => array_merge(...$Enums),
        ]);
    }

    public static function renderModel(JsonSchema4 $Schema, Config $Config, ?string $key = null, $array = false): array
    {
        $constants = [];
        $properties = [];
        $Enums = [];
        foreach ($Schema->properties as $property_name => $PropertySchema) {
            $psr_property_name = VarName::generate($property_name);
            $enum = null;
            $enum_values = null;
            $enum_comment = null;
            if ($PropertySchema->enum) {
                $enum_values = $PropertySchema->enum;
                $enum_comment = isset($PropertySchema->description) ? "/** $PropertySchema->description */" : null;
            } elseif ($PropertySchema->items?->enum) {
                $enum_values = $PropertySchema->items->enum;
                $enum_comment = isset($PropertySchema->items->description) ? "/** {$PropertySchema->items->description} */" : null;
            }
            if ($enum_values) {
                $enum = (isset($Config->namespace) ? '\\'.$Config->namespace.'\\' : null).Classname::generate(basename($property_name)).'Enum';
                $Enums[$psr_property_name] = [
                    Enum::comment => $enum_comment,
                    Enum::filename => Classname::generate($psr_property_name, 'Enum.php'),
                    Enum::backed_type => BackedEnumType::string,
                    Enum::cases => array_map(
                        static fn($value) => [
                            EnumCase::name => $value,
                            EnumCase::value => "'$value'"
                        ],
                        $enum_values,
                    ),
                ];
            }

            if (!$Config->exclude_constants
                && ($Config->comments || (isset($Config->constants->exclude_comments) && $Config->constants->exclude_comments))
            ) {
                $comment = $PropertySchema->description
                    ?
                    <<<PHP
                    /**
                     * $PropertySchema->description
                     *
                     * @see $$psr_property_name
                     */
                    PHP
                    : <<<PHP
                    /** @see $$psr_property_name */
                    PHP;
            }

            $constants[$psr_property_name] = [
                Constant::comment => $comment ?? null,
                Constant::value => "'$property_name'"
            ];

            $describe = [];
            if ($psr_property_name !== $property_name) {
                $describe[] = "'from' => self::$psr_property_name";
            }

            $is_nested = $PropertySchema->ref && isset($Schema->definitions[basename($PropertySchema->ref)])
                && $Schema->definitions[basename($PropertySchema->ref)]->type === 'array';
            $is_ref = $PropertySchema->ref && isset($Schema->definitions[basename($PropertySchema->ref)])
                && $Schema->definitions[basename($PropertySchema->ref)]->type === 'object';
            $is_inline = $PropertySchema->items?->type === 'object' && count($PropertySchema->items->properties);
            if ($is_nested || $is_inline || $is_ref) {
                $namespace = (isset($Config->namespace) ? '\\'.$Config->namespace.'\\' : null);
                if ($is_ref) {
                    $class = $namespace.Classname::generate($psr_property_name);
                } else {
                    $class = $namespace.Classname::generate(
                            $is_inline
                                ? $psr_property_name
                                : basename($PropertySchema->ref)
                        ).'Item';
                }

                $describe[] = "'cast' => [\\Zerotoprod\\DataModelHelper\\DataModelHelper::class, 'mapOf'], 'type' => $class::class";
                $description = $is_inline
                    ? $PropertySchema->description
                    : $Schema->definitions[basename($PropertySchema->ref)]->description;

                if ($is_ref) {
                    $comment = $description
                        ? <<<PHP
                        /** $description */
                        PHP
                        : null;
                } else {
                    $var_comment = "@var array<int|string, $class>";

                    $comment = $description
                        ? <<<PHP
                        /**
                         * $description
                         * $var_comment
                         */
                        PHP
                        : <<<PHP
                        /** $var_comment */
                        PHP;
                }
            } else {
                $comment = $PropertySchema->description
                    ? <<<PHP
                    /** $PropertySchema->description */
                    PHP
                    : null;
            }

            if ($is_nested) {
                $type = 'array';
            } elseif ($PropertySchema->type === 'object'
                && $PropertySchema->additionalProperties instanceof JsonSchema4
                && $PropertySchema->additionalProperties->type !== 'object'
            ) {
                $type = 'array';
            } else {
                $type = ($PropertySchema->type === 'object' || $PropertySchema->type === ['object'])
                    ? (isset($Config->namespace) ? '\\'.$Config->namespace.'\\' : null).Classname::generate($property_name)
                    : PropertyTypeResolver::resolve($PropertySchema, $Config);
            }

            if ($type === 'array' && !$is_nested) {
                if ($PropertySchema->type === 'object' && !$PropertySchema->additionalProperties) {
                    $types = (isset($Config->namespace) ? '\\'.$Config->namespace.'\\' : null).Classname::generate($property_name).'Item';
                } else {
                    $types = is_array($PropertySchema->items?->type)
                        ? implode('|', $PropertySchema->items?->type)
                        : $PropertySchema->items?->type;
                }

                if ($is_inline) {
                    $types = (isset($Config->namespace) ? '\\'.$Config->namespace.'\\' : null).Classname::generate($psr_property_name).'Item';
                }

                $joined = $PropertySchema?->description.' '.$PropertySchema->items?->description;
                $var = $types
                    ? "@var array<int, $types>"
                    : "@var array";
                $comment = ($PropertySchema->description || $PropertySchema->items?->description)
                    ? <<<PHP
                    /**
                     * $joined
                     * $var
                     */
                    PHP
                    : null;
            }

            if ($is_ref) {
                $type = (isset($Config->namespace) ? '\\'.$Config->namespace.'\\' : null).Classname::generate($psr_property_name);
            }

            $attributes = $describe && !$is_ref ? ["#[\\Zerotoprod\\DataModel\\Describe([".implode(', ', $describe)."])]"] : null;
            $comment = $comment
            && ($Config->comments || (isset($Config->properties->exclude_comments) && $Config->properties->exclude_comments))
                ? $comment
                : null;
            if ($PropertySchema->type === 'object'
                && !is_bool($PropertySchema->additionalProperties)
                && (
                    $PropertySchema->additionalProperties?->type === 'string'
                    || $PropertySchema->additionalProperties?->type === 'integer'
                    || $PropertySchema->additionalProperties?->type === 'array'
                )
            ) {
                $comment = $PropertySchema->description ? "/** $PropertySchema->description */" : null;
                $type = 'array';
            }

            if ($enum) {
                $type = $enum;
            }

            if ($enum && ($PropertySchema->type === 'array' || $PropertySchema->type === ['array'])) {
                $describe[] = "'cast' => [\\Zerotoprod\\DataModelHelper\\DataModelHelper::class, 'mapOf'], 'type' => $enum::class";
                $attributes = ["#[\\Zerotoprod\\DataModel\\Describe([".implode(', ', $describe)."])]"];
                $type = 'array';

                $comment = $PropertySchema->description
                    ? <<<PHP
                        /**
                         * $PropertySchema->description
                         * @var {$enum}[]
                         */
                        PHP
                    : <<<PHP
                        /** @var {$enum}[] */
                        PHP;
            }

            if ($PropertySchema->type === ['object'] && ($PropertySchema->additionalProperties?->type === 'string' || $PropertySchema->additionalProperties?->type === 'array')) {
                $type = 'array';
            }

            $properties[$psr_property_name] = [
                Property::attributes => $attributes,
                Property::comment => $comment,
                Property::type => $type,
            ];
        }

        return [
            "enums" => $Enums,
            "models" => [
                Model::filename => Classname::generate($key, $array ? 'Item.php' : '.php'),
                Model::comment => isset($Schema->description) && $Config->comments ? "/** $Schema->description */" : null,
                Model::constants => $constants,
                Model::properties => $properties,
            ],
        ];
    }

    public static function adapt2(string $open_api_30_schema, Config $Config): Components
    {
        $OpenApi = OpenApi::from(json_decode($open_api_30_schema, true));
        $Models = [];
        $Enums = [];
        foreach ($OpenApi->components->schemas as $name => $Schema) {
            if ($Schema->type === 'object') {
                $constants = [];
                if (!$Config->exclude_constants) {
                    foreach ($Schema->properties as $property_name => $PropertySchema) {
                        $property_name = VarName::generate($property_name);
                        if ($Config->comments || (isset($Config->constants->exclude_comments) && $Config->constants->exclude_comments)) {
                            $comment = isset($PropertySchema->description)
                                ?
                                <<<PHP
                                /**
                                 * $Schema->description
                                 *
                                 * @see $$property_name
                                 */
                                PHP
                                : <<<PHP
                                /** @see $$property_name */
                                PHP;
                        }

                        $constants[$property_name] = [
                            Constant::comment => $comment ?? null,
                            Constant::value => "'$property_name'"
                        ];
                    }
                }

                $Models[$name] = [
                    Model::comment => isset($Schema->description) && $Config->comments ? "/** $Schema->description */" : null,
                    Model::filename => Classname::generate($name, '.php'),
                    Model::properties => array_combine(
                        array_keys($Schema->properties),
                        array_map(
                            static function (string $property_name, Schema|Reference $Schema) use ($Config, &$Enums) {
                                $is_nested = isset($Schema->type) && $Schema->type === 'array' && $Schema->items instanceof Reference;
                                $comment = isset($Schema->description)
                                    ? <<<PHP
                                /** $Schema->description */
                                PHP
                                    : null;
                                if ($is_nested) {
                                    $class = (isset($Config->namespace) ? '\\'.$Config->namespace.'\\' : null).Classname::generate(basename($Schema->items->ref));
                                    $describe =
                                        ["#[\\Zerotoprod\\DataModel\\Describe(['cast' => [\\Zerotoprod\\DataModelHelper\\DataModelHelper::class, 'mapOf'], 'type' => $class::class])]"];

                                    $comment = isset($Schema->description)
                                        ? <<<PHP
                                        /**
                                         * $Schema->description 
                                         * @var array<int|string, $class>
                                         */
                                        PHP
                                        : <<<PHP
                                        /** @var array<int|string, $class> */
                                        PHP;
                                }
                                if (isset($Schema->type) && $Schema->type === 'string' && !empty($Schema->enum)) {
                                    $enum = (isset($Config->namespace) ? '\\'.$Config->namespace.'\\' : null).Classname::generate(basename($property_name)).'Enum';
                                    $Enums[$property_name] = [
                                        Enum::comment => isset($Schema->description) ? "/** $Schema->description */" : null,
                                        Enum::filename => Classname::generate($property_name, 'Enum.php'),
                                        Enum::backed_type => BackedEnumType::string,
                                        Enum::cases => array_map(
                                            static fn($value) => [
                                                EnumCase::name => $value,
                                                EnumCase::value => "'$value'"
                                            ],
                                            $Schema->enum,
                                        ),
                                    ];
                                }

                                return [
                                    Property::attributes => $describe ?? [],
                                    Property::comment => isset($Schema->description)
                                    && ($Config->comments || (isset($Config->properties->exclude_comments) && $Config->properties->exclude_comments))
                                        ? $comment
                                        : null,
                                    Property::type => $Schema instanceof Reference
                                        ? (isset($Config->namespace) ? '\\'.$Config->namespace.'\\' : null).Classname::generate(basename($Schema->ref))
                                        : PropertyTypeResolver::resolve($Schema, $Config, $enum ?? null),
                                ];
                            },
                            array_keys($Schema->properties),
                            $Schema->properties
                        ),
                    ),
                    Model::constants => $constants,
                ];
            }
        }

        return Components::from([
            Components::Config => $Config,
            Components::Models => $Models,
            Components::Enums => $Enums,
        ]);
    }
}