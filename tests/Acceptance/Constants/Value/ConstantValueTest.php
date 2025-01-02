<?php

namespace Tests\Acceptance\Constants\Value;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterOpenapi30\JsonSchema4Adapter;
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
                public const name = 'name';
                public string \$name;
                }
                PHP
        );
    }

    #[Test] public function disable_comment_constant(): void
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
                    ],
                ],
                Config::constants => [
                    ConstantConfig::exclude_comments => true
                ],
            ])
        );

        Engine::generate($Components);

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/User.php',
            actualString: <<<PHP
                <?php
                class User
                {
                public const name = 'name';
                public string \$name;
                }
                PHP
        );
    }

    #[Test] public function enable_comment_constant(): void
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
                    ],
                ],
                Config::constants => [
                    ConstantConfig::exclude_comments => false
                ],
                Config::namespace => 'App\\DataModels'
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
                /** @see \$name */
                public const name = 'name';
                public string \$name;
                }
                PHP
        );
    }

    #[Test] public function enable_comment_config(): void
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
                    ],
                ],
                ConstantConfig::exclude_comments => true,
                Config::constants => [
                    ConstantConfig::exclude_comments => false
                ],
            ])
        );

        Engine::generate($Components);

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/User.php',
            actualString: <<<PHP
                <?php
                class User
                {
                /** @see \$name */
                public const name = 'name';
                public string \$name;
                }
                PHP
        );
    }
}