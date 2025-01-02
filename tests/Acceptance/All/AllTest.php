<?php

namespace Tests\Acceptance\All;

use PHPUnit\Framework\Attributes\Test;
use Tests\generated\AuthorsItem;
use Tests\generated\ComposerPackage;
use Tests\generated\FundingItem;
use Tests\generated\Support;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterOpenapi30\JsonSchema4Adapter;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config;
use Zerotoprod\DataModelGenerator\Models\ModelConfig;

class AllTest extends TestCase
{
    #[Test] public function all(): void
    {
        $this->generate();

        $ComposerPackage = ComposerPackage::from([
            ComposerPackage::name => 'name',
            ComposerPackage::description => 'description',
            ComposerPackage::license => 'license',
            ComposerPackage::type => 'type',
            ComposerPackage::abandoned => false,
            ComposerPackage::version => 'version',
            ComposerPackage::default_branch => true,
            ComposerPackage::non_feature_branches => ['main'],
            ComposerPackage::keywords => ['word1', 'word2'],
            ComposerPackage::readme => 'readme',
            ComposerPackage::time => 'time',
            ComposerPackage::authors => [
                AuthorsItem::from([
                    AuthorsItem::name => 'name',
                    AuthorsItem::email => 'email',
                    AuthorsItem::homepage => 'homepage',
                    AuthorsItem::role => 'role',
                ])
            ],
            ComposerPackage::homepage => 'homepage',
            ComposerPackage::support => Support::from([
                Support::email => 'email',
                Support::issues => 'issues',
                Support::forum => 'forum',
                Support::wiki => 'wiki',
                Support::irc => 'irc',
                Support::chat => 'chat',
                Support::source => 'source',
                Support::docs => 'docs',
                Support::rss => 'rss',
                Support::security => 'security',
            ]),
            ComposerPackage::funding => [
                FundingItem::from([
                    FundingItem::type => 'type',
                ])
            ]
        ]);

        self::assertEquals('name', $ComposerPackage->name);
        self::assertEquals('description', $ComposerPackage->description);
        self::assertEquals('license', $ComposerPackage->license);
        self::assertEquals('type', $ComposerPackage->type);
        self::assertFalse($ComposerPackage->abandoned);
        self::assertEquals('version', $ComposerPackage->version);
        self::assertTrue($ComposerPackage->default_branch);
        self::assertEquals(['main'], $ComposerPackage->non_feature_branches);
        self::assertEquals(['word1', 'word2'], $ComposerPackage->keywords);
        self::assertEquals('readme', $ComposerPackage->readme);
        self::assertEquals('time', $ComposerPackage->time);
        self::assertEquals('name', $ComposerPackage->authors[0]->name);
        self::assertEquals('email', $ComposerPackage->authors[0]->email);
        self::assertEquals('homepage', $ComposerPackage->authors[0]->homepage);
        self::assertEquals('role', $ComposerPackage->authors[0]->role);
        self::assertEquals('homepage', $ComposerPackage->homepage);
        self::assertEquals('email', $ComposerPackage->support->email);
        self::assertEquals('issues', $ComposerPackage->support->issues);
        self::assertEquals('forum', $ComposerPackage->support->forum);
        self::assertEquals('wiki', $ComposerPackage->support->wiki);
        self::assertEquals('irc', $ComposerPackage->support->irc);
        self::assertEquals('chat', $ComposerPackage->support->chat);
        self::assertEquals('source', $ComposerPackage->support->source);
        self::assertEquals('docs', $ComposerPackage->support->docs);
        self::assertEquals('rss', $ComposerPackage->support->rss);
        self::assertEquals('security', $ComposerPackage->support->security);
        self::assertEquals('type', $ComposerPackage->funding[0]->type);
    }

    #[Test] public function licence_array(): void
    {
        $this->generate();

        $ComposerPackage = ComposerPackage::from([
            ComposerPackage::license => ['license1', 'license2'],
        ]);

        self::assertEquals(['license1', 'license2'], $ComposerPackage->license);
    }

    #[Test] public function abandoned_boolean(): void
    {
        $this->generate();

        $ComposerPackage = ComposerPackage::from([
            ComposerPackage::abandoned => true,
        ]);

        self::assertTrue($ComposerPackage->abandoned);
    }

    #[Test] public function abandoned_string(): void
    {
        $this->generate();

        $ComposerPackage = ComposerPackage::from([
            ComposerPackage::abandoned => 'https://other.com',
        ]);

        self::assertEquals('https://other.com', $ComposerPackage->abandoned);
    }

    private function generate(): void
    {
        $Components = JsonSchema4Adapter::adapt(
            file_get_contents(__DIR__.'/json-schema4.json'),
            Config::from([
                Config::directory => self::$test_dir,
                Config::properties => [],
                Config::namespace => 'Tests\\generated',
                Config::model => [
                    ModelConfig::use_statements => ['use \\Zerotoprod\\DataModel\\DataModel;']
                ]
            ])
        );

        Engine::generate($Components);
    }
}