<?php

namespace Zerotoprod\DataModelAdapterOpenapi30\Resolvers;

use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\JsonSchema4\JsonSchema4;
use Zerotoprod\Psr4Classname\Classname;

class PropertyTypeResolver
{
    public static function resolve(JsonSchema4 $Schema, Config $Config, ?string $enum = null): string
    {
        if ($enum) {
            $types = [$enum];
        } else {
            $types = array_filter(
                array_map(
                    static fn(JsonSchema4 $Schema) => self::resolveType($Config, $Schema),
                    array_merge(
                        [$Schema],
                        $Schema->oneOf ?? [],
                        $Schema->anyOf ?? []
                    )
                )
            );
        }

//        if ($Schema->nullable) {
//            $types[] = 'null';
//        }

        return implode('|', array_unique($types));
    }

    private static function resolveType(Config $Config, JsonSchema4 $Schema): ?string
    {
//        if ($Schema->type instanceof Reference) {
//            return (isset($Config->namespace) ? '\\'.$Config->namespace.'\\' : null).Classname::generate(basename($Schema->ref));
//        }

        if (is_array($Schema->type)) {
            return implode(
                "|",
                array_map(
                    static fn(string $type) => $Config->properties->types[$Schema->format]->type
                        ?? match ($type) {
                            'number' => 'float',
                            'integer' => 'int',
                            'boolean' => 'bool',
                            default => $type,
                        }
                    , $Schema->type
                )
            );
        }

        return $Config->properties->types[$Schema->format]->type
            ?? match ($Schema->type) {
                'number' => 'float',
                'integer' => 'int',
                'boolean' => 'bool',
                default => $Schema->type,
            };
    }
}