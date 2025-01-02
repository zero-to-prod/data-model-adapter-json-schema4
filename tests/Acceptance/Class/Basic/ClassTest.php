<?php

namespace Tests\Acceptance\Class\Basic;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterJsonSchema4\JsonSchema4Adapter;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;

class ClassTest extends TestCase
{
    #[Test] public function generate(): void
    {
        Engine::generate(
            JsonSchema4Adapter::adapt(
                file_get_contents(__DIR__.'/json-schema4.json'),
                Config::from([Config::directory => self::$test_dir])
            )
        );

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/ComposerPackage.php',
            actualString: <<<PHP
                <?php
                class ComposerPackage
                {
                }
                PHP
        );
    }
}