<?php

namespace Tests\Acceptance\Properties\Ref;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterJsonSchema4\JsonSchema4Adapter;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\PropertyConfig;
use Zerotoprod\DataModelGenerator\Models\Type;

class RefTest extends TestCase
{
    #[Test] public function generate(): void
    {
        $Components = JsonSchema4Adapter::adapt(
            file_get_contents(__DIR__.'/json-schema4.json'),
            Config::from([
                Config::directory => self::$test_dir,
            ])
        );

        Engine::generate($Components);

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/User.php',
            actualString: <<<PHP
                <?php
                class User
                {
                /** @see \$source */
                public const source = 'source';
                /** description */
                public Source \$source;
                }
                PHP
        );

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/Source.php',
            actualString: <<<PHP
                <?php
                /** description */
                class Source
                {
                /** @see \$type */
                public const type = 'type';
                public string \$type;
                }
                PHP
        );
    }
}