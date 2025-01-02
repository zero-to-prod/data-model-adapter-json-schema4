<?php

namespace Tests\Acceptance\Properties\Visibility;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterOpenapi30\JsonSchema4Adapter;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\PropertyConfig;
use Zerotoprod\DataModelGenerator\Models\Visibility;

class VisibilityTest extends TestCase
{
    #[Test] public function generate(): void
    {
        Engine::generate(
            JsonSchema4Adapter::adapt(
                file_get_contents(__DIR__.'/json-schema4.json'),
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