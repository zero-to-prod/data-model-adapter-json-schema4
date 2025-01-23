<?php

namespace Tests\Acceptance\Properties\InlineObject;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterJsonSchema4\JsonSchema4;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;

class InlineObjectTest extends TestCase
{
    #[Test] public function generate(): void
    {
        $Components = JsonSchema4::adapt(
            json_decode(file_get_contents(__DIR__.'/json-schema4.json'), true),
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
                 * A list of options to fund the development and maintenance of the package. 
                 * @var array<int, FundingItem>
                 */
                #[\Zerotoprod\DataModel\Describe(['cast' => [\Zerotoprod\DataModelHelper\DataModelHelper::class, 'mapOf'], 'type' => FundingItem::class])]
                public array \$funding;
                }
                PHP
        );

        self::assertStringEqualsFile(
            expectedFile: self::$test_dir.'/FundingItem.php',
            actualString: <<<PHP
                <?php
                class FundingItem
                {
                /** Type of funding or platform through which funding is possible. */
                public string \$type;
                /** URL to a website with details on funding and a way to fund the package. */
                public string \$url;
                }
                PHP
        );
    }
}