<?php

namespace Tests\Acceptance\Constants\Comments;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterJsonSchema4\JsonSchema4;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;

class ConstantCommentTest extends TestCase
{
    #[Test] public function generate(): void
    {
        $Components = JsonSchema4::adapt(
            json_decode(file_get_contents(__DIR__.'/json-schema4.json'), true),
            Config::from([
                Config::directory => self::$test_dir
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