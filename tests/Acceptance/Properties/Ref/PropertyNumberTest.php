<?php

namespace Tests\Acceptance\Properties\Ref;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterOpenapi30\JsonSchema4Adapter;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\PropertyConfig;
use Zerotoprod\DataModelGenerator\Models\Type;

class PropertyNumberTest extends TestCase
{
    #[Test] public function generate(): void
    {
        $this->markTestSkipped();
        $Components = JsonSchema4Adapter::adapt(
            file_get_contents(__DIR__.'/openapi30.json'),
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
                Config::namespace => 'App\\DataModels',
            ])
        );

        Engine::generate($Components);

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/User.php',
            actualString: <<<PHP
                <?php
                namespace App\DataModels;
                class User
                {
                public \App\DataModels\LastName \$LastName;
                }
                PHP
        );
    }
}