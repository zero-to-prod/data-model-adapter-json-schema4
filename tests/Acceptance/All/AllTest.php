<?php

namespace Tests\Acceptance\All;

use PHPUnit\Framework\Attributes\Test;
use Tests\generated\Archive;
use Tests\generated\AuthorsItem;
use Tests\generated\Autoload;
use Tests\generated\AutoloadDev;
use Tests\generated\ComposerPackage;
use Tests\generated\Config;
use Tests\generated\ConfigureOptionsItem;
use Tests\generated\Dist;
use Tests\generated\FundingItem;
use Tests\generated\MinimumStabilityEnum;
use Tests\generated\OsFamiliesEnum;
use Tests\generated\OsFamiliesExcludeEnum;
use Tests\generated\PhpExt;
use Tests\generated\Scripts;
use Tests\generated\Source;
use Tests\generated\Support;
use Tests\TestCase;
use Zerotoprod\DataModelAdapterJsonSchema4\JsonSchema4;
use Zerotoprod\DataModelGenerator\Engine;
use Zerotoprod\DataModelGenerator\Models\Config as ConfigAlias;
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
                [
                    AuthorsItem::name => 'name',
                    AuthorsItem::email => 'email',
                    AuthorsItem::homepage => 'homepage',
                    AuthorsItem::role => 'role',
                ]
            ],
            ComposerPackage::homepage => 'homepage',
            ComposerPackage::support => [
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
            ],
            ComposerPackage::funding => [
                [
                    FundingItem::type => 'type',
                    FundingItem::url => 'url',
                ]
            ],
            ComposerPackage::source => [
                Source::type => 'type',
                Source::url => 'url',
                Source::reference => 'reference',
                Source::mirrors => ['mirrors'],
            ],
            ComposerPackage::dist => [
                Dist::type => 'type',
                Dist::url => 'url',
                Dist::reference => 'reference',
                Dist::shasum => 'shasum',
                Dist::mirrors => ['mirrors'],
            ],
            ComposerPackage::_comment => 'comment',
            ComposerPackage::require => ['monolog/monolog' => '2.0.*'],
            ComposerPackage::require_dev => ['php' => '>=8.1.0'],
            ComposerPackage::replace => ['php' => '>=8.1.0'],
            ComposerPackage::conflict => ['php' => '>=8.1.0'],
            ComposerPackage::provide => ['php' => '>=8.1.0'],
            ComposerPackage::suggest => ['php' => '>=8.1.0'],
            ComposerPackage::minimum_stability => 'stable',
            ComposerPackage::prefer_stable => true,
            ComposerPackage::autoload => [
                Autoload::psr_0 => ["Vendor\\Package\\" => "src/"],
                Autoload::psr_4 => ["Vendor\\Package\\" => "src/"],
                Autoload::classmap => ['./path/1'],
                Autoload::files => ['./path/1'],
                Autoload::exclude_from_classmap => ['./path/1'],
            ],
            ComposerPackage::autoload_dev => [
                AutoloadDev::psr_0 => ["Vendor\\Package\\" => "src/"],
                AutoloadDev::psr_4 => ["Vendor\\Package\\" => "src/"],
                AutoloadDev::classmap => ['./path/1'],
                AutoloadDev::files => ['./path/1'],
            ],
            ComposerPackage::target_dir => '/target/dir',
            ComposerPackage::include_path => ['/target/dir'],
            ComposerPackage::bin => './bin',
            ComposerPackage::archive => [
                Archive::name => 'name',
                Archive::exclude => ['./path/1', './path/2'],
            ],
            ComposerPackage::php_ext => [
                PhpExt::extension_name => 'extension_name',
                PhpExt::priority => 10,
                PhpExt::support_zts => true,
                PhpExt::support_nts => true,
                PhpExt::build_path => 'build_path',
                PhpExt::os_families => [
                    OsFamiliesEnum::bsd->value
                ],
                PhpExt::os_families_exclude => [
                    OsFamiliesExcludeEnum::bsd->value
                ],
                PhpExt::configure_options => [
                    [
                        ConfigureOptionsItem::name => 'name',
                        ConfigureOptionsItem::description => 'description',
                        ConfigureOptionsItem::needs_value => true,
                    ]
                ]
            ],
            ComposerPackage::config => [
                Config::platform => ['a']
            ],
            ComposerPackage::extra => [
                'laravel' => [
                    'dont-discover' => []
                ]
            ],
            ComposerPackage::scripts => [
                Scripts::pre_install_cmd => Scripts::pre_install_cmd,
                Scripts::post_install_cmd => Scripts::post_install_cmd,
                Scripts::pre_update_cmd => Scripts::pre_update_cmd,
                Scripts::post_update_cmd => Scripts::post_update_cmd,
                Scripts::pre_status_cmd => Scripts::pre_status_cmd,
                Scripts::post_status_cmd => Scripts::post_status_cmd,
                Scripts::pre_package_install => Scripts::pre_package_install,
                Scripts::post_package_install => Scripts::post_package_install,
                Scripts::pre_package_update => Scripts::pre_package_update,
                Scripts::post_package_update => Scripts::post_package_update,
                Scripts::pre_package_uninstall => Scripts::pre_package_uninstall,
                Scripts::post_package_uninstall => Scripts::post_package_uninstall,
                Scripts::pre_autoload_dump => Scripts::pre_autoload_dump,
                Scripts::post_autoload_dump => Scripts::post_autoload_dump,
                Scripts::post_root_package_install => Scripts::post_root_package_install,
                Scripts::post_create_project_cmd => Scripts::post_create_project_cmd,
            ],
            ComposerPackage::scripts_descriptions => [
                'key' => 'value'
            ],
            ComposerPackage::scripts_aliases => [
                ['key' => 'value']
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
        self::assertEquals('url', $ComposerPackage->funding[0]->url);
        self::assertEquals('type', $ComposerPackage->source->type);
        self::assertEquals('url', $ComposerPackage->source->url);
        self::assertEquals('reference', $ComposerPackage->source->reference);
        self::assertEquals(['mirrors'], $ComposerPackage->source->mirrors);
        self::assertEquals('type', $ComposerPackage->dist->type);
        self::assertEquals('url', $ComposerPackage->dist->url);
        self::assertEquals('reference', $ComposerPackage->dist->reference);
        self::assertEquals('shasum', $ComposerPackage->dist->shasum);
        self::assertEquals(['mirrors'], $ComposerPackage->dist->mirrors);
        self::assertEquals('comment', $ComposerPackage->_comment);
        self::assertEquals('2.0.*', $ComposerPackage->require['monolog/monolog']);
        self::assertEquals('>=8.1.0', $ComposerPackage->require_dev['php']);
        self::assertEquals('>=8.1.0', $ComposerPackage->replace['php']);
        self::assertEquals('>=8.1.0', $ComposerPackage->conflict['php']);
        self::assertEquals('>=8.1.0', $ComposerPackage->provide['php']);
        self::assertEquals('>=8.1.0', $ComposerPackage->suggest['php']);
        self::assertEquals(MinimumStabilityEnum::stable, $ComposerPackage->minimum_stability);
        self::assertTrue($ComposerPackage->prefer_stable);
        self::assertEquals('src/', $ComposerPackage->autoload->psr_0["Vendor\\Package\\"]);
        self::assertEquals('src/', $ComposerPackage->autoload->psr_4["Vendor\\Package\\"]);
        self::assertEquals('./path/1', $ComposerPackage->autoload->classmap[0]);
        self::assertEquals('./path/1', $ComposerPackage->autoload->files[0]);
        self::assertEquals('./path/1', $ComposerPackage->autoload->exclude_from_classmap[0]);
        self::assertEquals('src/', $ComposerPackage->autoload_dev->psr_0["Vendor\\Package\\"]);
        self::assertEquals('src/', $ComposerPackage->autoload_dev->psr_4["Vendor\\Package\\"]);
        self::assertEquals('./path/1', $ComposerPackage->autoload_dev->classmap[0]);
        self::assertEquals('./path/1', $ComposerPackage->autoload_dev->files[0]);
        self::assertEquals('/target/dir', $ComposerPackage->target_dir);
        self::assertEquals(['/target/dir'], $ComposerPackage->include_path);
        self::assertEquals('./bin', $ComposerPackage->bin);
        self::assertEquals('name', $ComposerPackage->archive->name);
        self::assertEquals(['./path/1', './path/2'], $ComposerPackage->archive->exclude);
        self::assertEquals('extension_name', $ComposerPackage->php_ext->extension_name);
        self::assertEquals(10, $ComposerPackage->php_ext->priority);
        self::assertTrue($ComposerPackage->php_ext->support_zts);
        self::assertTrue($ComposerPackage->php_ext->support_nts);
        self::assertEquals('build_path', $ComposerPackage->php_ext->build_path);
        self::assertEquals(OsFamiliesEnum::bsd, $ComposerPackage->php_ext->os_families[0]);
        self::assertEquals(OsFamiliesExcludeEnum::bsd, $ComposerPackage->php_ext->os_families_exclude[0]);
        self::assertEquals('name', $ComposerPackage->php_ext->configure_options[0]->name);
        self::assertEquals('description', $ComposerPackage->php_ext->configure_options[0]->description);
        self::assertTrue($ComposerPackage->php_ext->configure_options[0]->needs_value);
        self::assertEquals([], $ComposerPackage->extra['laravel']['dont-discover']);
        self::assertEquals(Scripts::pre_install_cmd, $ComposerPackage->scripts->pre_install_cmd);
        self::assertEquals(Scripts::post_install_cmd, $ComposerPackage->scripts->post_install_cmd);
        self::assertEquals(Scripts::pre_update_cmd, $ComposerPackage->scripts->pre_update_cmd);
        self::assertEquals(Scripts::post_update_cmd, $ComposerPackage->scripts->post_update_cmd);
        self::assertEquals(Scripts::pre_status_cmd, $ComposerPackage->scripts->pre_status_cmd);
        self::assertEquals(Scripts::post_status_cmd, $ComposerPackage->scripts->post_status_cmd);
        self::assertEquals(Scripts::pre_package_install, $ComposerPackage->scripts->pre_package_install);
        self::assertEquals(Scripts::post_package_install, $ComposerPackage->scripts->post_package_install);
        self::assertEquals(Scripts::pre_package_update, $ComposerPackage->scripts->pre_package_update);
        self::assertEquals(Scripts::post_package_update, $ComposerPackage->scripts->post_package_update);
        self::assertEquals(Scripts::pre_package_uninstall, $ComposerPackage->scripts->pre_package_uninstall);
        self::assertEquals(Scripts::post_package_uninstall, $ComposerPackage->scripts->post_package_uninstall);
        self::assertEquals(Scripts::pre_autoload_dump, $ComposerPackage->scripts->pre_autoload_dump);
        self::assertEquals(Scripts::post_autoload_dump, $ComposerPackage->scripts->post_autoload_dump);
        self::assertEquals(Scripts::post_root_package_install, $ComposerPackage->scripts->post_root_package_install);
        self::assertEquals(Scripts::post_create_project_cmd, $ComposerPackage->scripts->post_create_project_cmd);
        self::assertEquals('value', $ComposerPackage->scripts_descriptions['key']);
        self::assertEquals(['key' => 'value'], $ComposerPackage->scripts_aliases[0]);
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

    #[Test] public function comment_array(): void
    {
        $this->generate();

        $ComposerPackage = ComposerPackage::from([
            ComposerPackage::_comment => ['comment1', 'comment2'],
        ]);

        self::assertEquals(['comment1', 'comment2'], $ComposerPackage->_comment);
    }

    #[Test] public function bin_array(): void
    {
        $this->generate();

        $ComposerPackage = ComposerPackage::from([
            ComposerPackage::bin => ['bin/file', 'bin/file2'],
        ]);

        self::assertEquals(['bin/file', 'bin/file2'], $ComposerPackage->bin);
    }

    private function generate(): void
    {
        $Components = JsonSchema4::adapt(
            json_decode(file_get_contents(__DIR__.'/json-schema4.json'), true),
            ConfigAlias::from([
                ConfigAlias::directory => self::$test_dir,
                ConfigAlias::properties => [],
                ConfigAlias::namespace => 'Tests\\generated',
                ConfigAlias::model => [
                    ModelConfig::use_statements => ['use \\Zerotoprod\\DataModel\\DataModel;']
                ]
            ])
        );

        Engine::generate($Components);
    }

}