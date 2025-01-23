<?php

namespace Tests\Unit\Definitions\Object;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterJsonSchema4\JsonSchema4;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;

class ObjectTest extends TestCase
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
            expectedFile: self::$test_dir.'/ComposerPackage.php',
            actualString: <<<PHP
                <?php
                class ComposerPackage
                {
                /** @see \$author */
                public const author = 'author';
                public Author \$author;
                }
                PHP
        );

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/Author.php',
            actualString: <<<PHP
                <?php
                class Author
                {
                /** @see \$name */
                public const name = 'name';
                public string \$name;
                }
                PHP
        );
    }
}