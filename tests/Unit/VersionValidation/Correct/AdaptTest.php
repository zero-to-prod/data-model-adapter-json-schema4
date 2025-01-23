<?php

namespace Tests\Unit\VersionValidation\Correct;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterJsonSchema4\JsonSchema4;
use Zerotoprod\DataModelGenerator\Models\Components;
use Zerotoprod\DataModelGenerator\Models\Config;

class AdaptTest extends TestCase
{
    #[Test] public function correct_version_validation(): void
    {
        self::assertTrue(
            is_a(
                object_or_class: JsonSchema4::adapt(
                    json_decode(file_get_contents(__DIR__.'/json-schema4.json'), true),
                    Config::from([
                        Config::directory => self::$test_dir
                    ])
                ),
                class: Components::class
            )
        );
    }
}