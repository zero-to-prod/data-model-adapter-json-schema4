<?php

namespace Tests\Unit\Definitions\Object;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterOpenapi30\JsonSchema4Adapter;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Components;
use Zerotoprod\DataModelGenerator\Models\Config;

class ObjectTest extends TestCase
{
    #[Test] public function generate(): void
    {
        $Components = JsonSchema4Adapter::adapt(
            file_get_contents(__DIR__.'/json-schema4.json'),
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