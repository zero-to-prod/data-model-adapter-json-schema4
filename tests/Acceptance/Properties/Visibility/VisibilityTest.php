<?php

namespace Tests\Acceptance\Properties\Visibility;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterJsonSchema4\JsonSchema4;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\PropertyConfig;
use Zerotoprod\DataModelGenerator\Models\Visibility;

class VisibilityTest extends TestCase
{
    #[Test] public function generate(): void
    {
        Engine::generate(
            JsonSchema4::adapt(
                json_decode(file_get_contents(__DIR__.'/json-schema4.json'), true),
                Config::from([
                    Config::directory => self::$test_dir,
                    Config::properties => [
                        PropertyConfig::visibility => Visibility::protected
                    ],
                    Config::exclude_constants => true,
                ])
            )
        );

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/User.php',
            actualString: <<<PHP
                <?php
                class User
                {
                protected float \$age;
                }
                PHP
        );
    }
}