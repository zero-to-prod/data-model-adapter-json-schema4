<?php

namespace Tests\Unit\Definitions\Array;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterOpenapi30\JsonSchema4Adapter;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Components;
use Zerotoprod\DataModelGenerator\Models\Config;

class ArrayTest extends TestCase
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
                /** @see \$authors */
                public const authors = 'authors';
                /**
                 * List of authors that contributed to the package. This is typically the main maintainers, not the full list. 
                 * @var array<int|string, AuthorsItem>
                 */
                #[\Zerotoprod\DataModel\Describe(['cast' => [\Zerotoprod\DataModelHelper\DataModelHelper::class, 'mapOf'], 'type' => AuthorsItem::class])]
                public array \$authors;
                }
                PHP
        );

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/AuthorsItem.php',
            actualString: <<<PHP
                <?php
                class AuthorsItem
                {
                /**
                 * Full name of the author.
                 *
                 * @see \$name
                 */
                public const name = 'name';
                /** Full name of the author. */
                public string \$name;
                }
                PHP
        );
    }
}