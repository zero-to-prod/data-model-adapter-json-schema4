<?php

namespace Zerotoprod\DataModelAdapterOpenapi30;

use Zerotoprod\DataModelAdapterOpenapi30\Resolvers\PropertyTypeResolver;
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

class JsonSchema4Adapter
{

    public static function renderModel(JsonSchema4 $Schema, Config $Config, ?string $key = null, $array = false): array
    {
        $constants = null;
        $properties = null;
        foreach ($Schema->properties as $property_name => $PropertySchema) {
            $psr_property_name = VarName::generate($property_name);

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

            if ($is_nested) {
                $class = (isset($Config->namespace) ? '\\'.$Config->namespace.'\\' : null).Classname::generate(basename($PropertySchema->ref)).'Item';
                $describe[] = "'cast' => [\\Zerotoprod\\DataModelHelper\\DataModelHelper::class, 'mapOf'], 'type' => $class::class";
                $description = $Schema->definitions[basename($PropertySchema->ref)]->description;

                $comment = $description
                    ? <<<PHP
                    /**
                     * $description 
                     * @var array<int|string, $class>
                     */
                    PHP
                    : <<<PHP
                    /** @var array<int|string, $class> */
                    PHP;
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
                $type = $PropertySchema->type === 'object'
                    ? (isset($Config->namespace) ? '\\'.$Config->namespace.'\\' : null).Classname::generate($property_name)
                    : PropertyTypeResolver::resolve($PropertySchema, $Config);
            }

            if ($type === 'array' && !$is_nested) {
                if ($PropertySchema->type === 'object') {
                    $types = (isset($Config->namespace) ? '\\'.$Config->namespace.'\\' : null).Classname::generate($property_name).'Item';
                } else {
                    $types = is_array($PropertySchema->items?->type)
                        ? implode('|', $PropertySchema->items?->type)
                        : $PropertySchema->items?->type;
                }

                $joined = $PropertySchema?->description.' '.$PropertySchema->items?->description;

                $comment = ($PropertySchema->description || $PropertySchema->items->description)
                    ? <<<PHP
                    /**
                     * $joined
                     * @var array<int, $types>
                     */
                    PHP
                    : <<<PHP
                    /**
                     * @var array<int, $types>
                     */
                    PHP;
            }

            $properties[$psr_property_name] = [
                Property::attributes => $describe ? ["#[\\Zerotoprod\\DataModel\\Describe([".implode(', ', $describe)."])]"] : null,
                Property::comment => $comment
                && ($Config->comments || (isset($Config->properties->exclude_comments) && $Config->properties->exclude_comments))
                    ? $comment
                    : null,
                Property::type => $type,
            ];
        }

        return [
            Model::filename => Classname::generate($key, $array ? 'Item.php' : '.php'),
            Model::comment => isset($Schema->description) && $Config->comments ? "/** $Schema->description */" : null,
            Model::constants => $constants,
            Model::properties => $properties,
        ];
    }

    public static function adapt(string $open_api_30_schema, Config $Config): Components
    {
        $JsonSchema4 = JsonSchema4::from(json_decode($open_api_30_schema, true));
        $Models = [];
        $Models[] = self::renderModel($JsonSchema4, $Config, $JsonSchema4->title);

        foreach ($JsonSchema4->properties as $key => $Schema) {
            if ($Schema->type === 'object' && !$Schema->additionalProperties) {
                $Models[] = self::renderModel($Schema, $Config, $key);
            }
            if ($Schema->items?->type === 'object') {
                $Models[] = self::renderModel($JsonSchema4::from($Schema->items), $Config, $key, true);
            }
        }

        foreach ($JsonSchema4->definitions as $key => $Schema) {
            if ($Schema->items?->type === 'object') {
                $Models[] = self::renderModel($JsonSchema4::from($Schema->items), $Config, $key, true);
            }
        }
        $Enums = [];

        return Components::from([
            Components::Config => $Config,
            Components::Models => $Models,
            Components::Enums => $Enums,
        ]);
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