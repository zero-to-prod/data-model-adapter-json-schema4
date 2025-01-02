<?php

namespace Tests\Acceptance\Constants\NonPsrValue;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterJsonSchema4\JsonSchema4Adapter;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\ConstantConfig;
use Zerotoprod\DataModelGenerator\Models\PropertyConfig;
use Zerotoprod\DataModelGenerator\Models\Type;

class ConstantValueTest extends TestCase
{
    #[Test] public function generate(): void
    {
        $Components = JsonSchema4Adapter::adapt(
            file_get_contents(__DIR__.'/json-schema4.json'),
            Config::from([
                Config::directory => self::$test_dir,
                Config::comments => false,
            ])
        );

        Engine::generate($Components);

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/User.php',
            actualString: <<<PHP
                <?php
                class User
                {
                public const first_name = 'first-name';
                #[\Zerotoprod\DataModel\Describe(['from' => self::first_name])]
                public string \$first_name;
                }
                PHP
        );
    }
}