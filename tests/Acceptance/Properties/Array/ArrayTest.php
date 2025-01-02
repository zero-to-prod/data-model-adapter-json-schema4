<?php

namespace Tests\Acceptance\Properties\Array;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterJsonSchema4\JsonSchema4Adapter;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\PropertyConfig;
use Zerotoprod\DataModelGenerator\Models\Type;

class ArrayTest extends TestCase
{
    #[Test] public function generate(): void
    {
        $Components = JsonSchema4Adapter::adapt(
            file_get_contents(__DIR__.'/json-schema4.json'),
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
                /**
                 * description Item Description
                 * @var array<int, string|integer>
                 */
                public array \$active;
                }
                PHP
        );
    }
}