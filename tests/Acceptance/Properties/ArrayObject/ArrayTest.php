<?php

namespace Tests\Acceptance\Properties\ArrayObject;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterJsonSchema4\JsonSchema4;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;

class ArrayTest extends TestCase
{
    #[Test] public function generate(): void
    {
        $Components = JsonSchema4::adapt(
            json_decode(file_get_contents(__DIR__.'/json-schema4.json'), true),
            Config::from([
                Config::directory => self::$test_dir,
                Config::exclude_constants => true,
            ])
        );

        Engine::generate($Components);

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/User.php',
            actualString: <<<PHP
                <?php
                class User
                {
                /** description */
                public array \$require;
                }
                PHP
        );
    }
}