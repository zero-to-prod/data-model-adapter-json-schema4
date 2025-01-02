<?php

namespace Tests\Acceptance\Properties\Integer;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterJsonSchema4\JsonSchema4Adapter;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\PropertyConfig;
use Zerotoprod\DataModelGenerator\Models\Type;

class PropertyNumberTest extends TestCase
{
    #[Test] public function generate(): void
    {
        $Components = JsonSchema4Adapter::adapt(
            file_get_contents(__DIR__.'/json-schema4.json'),
            Config::from([
                Config::directory => self::$test_dir,
                Config::properties => [
                    PropertyConfig::types => [
                        'int32' => [
                            Type::type => 'string'
                        ],
                    ]
                ],
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
                public int \$age;
                public string \$age2;
                }
                PHP
        );
    }
}