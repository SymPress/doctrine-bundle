<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable -- The test controls WordPress globals.

namespace SymPress\DoctrineBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SymPress\DoctrineBundle\Dbal\WordPressTablesSchemaFilter;
use SymPress\Kernel\EnvConfig;

final class WordPressTablesSchemaFilterTest extends TestCase
{
    private mixed $envPrefix;

    protected function setUp(): void
    {
        // PHPUnit configures DB_TABLE_PREFIX for the kernel suites; these tests choose the source themselves.
        $this->envPrefix = $_ENV['DB_TABLE_PREFIX'] ?? null;
        unset($_ENV['DB_TABLE_PREFIX'], $_SERVER['DB_TABLE_PREFIX']);
        putenv('DB_TABLE_PREFIX');
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['wpdb'], $GLOBALS['table_prefix'], $_ENV['DB_TABLE_PREFIX']);
        if (!is_string($this->envPrefix)) {
            return;
        }

        $_ENV['DB_TABLE_PREFIX'] = $this->envPrefix;
        putenv('DB_TABLE_PREFIX=' . $this->envPrefix);
    }

    public function testHidesSingleAndMultisiteTablesOfTheWpdbBasePrefix(): void
    {
        $GLOBALS['wpdb'] = (object) ['base_prefix' => 'site_', 'prefix' => 'site_2_'];
        $GLOBALS['table_prefix'] = 'ignored_';
        $filter = new WordPressTablesSchemaFilter(new EnvConfig());

        self::assertFalse($filter('site_posts'));
        self::assertFalse($filter('site_2_posts'));
        self::assertFalse($filter('SITE_USERS'));
        self::assertFalse($filter('public.site_options'));
        self::assertTrue($filter('enterprise_invoice'));
        self::assertTrue($filter('ignored_posts'));
        self::assertTrue($filter('doctrine_migration_versions'));
    }

    public function testFallsBackToTheTablePrefixGlobalThenTheEnvironment(): void
    {
        $_ENV['DB_TABLE_PREFIX'] = 'env_';
        $filter = new WordPressTablesSchemaFilter(new EnvConfig());
        self::assertFalse($filter('env_posts'));

        $GLOBALS['table_prefix'] = 'config_';
        self::assertFalse($filter('config_posts'));
        self::assertTrue($filter('env_posts'));
    }

    public function testRefusesEverySchemaAssetWhenThePrefixIsUnknown(): void
    {
        try {
            (new WordPressTablesSchemaFilter(new EnvConfig()))('enterprise_invoice');
            self::fail('The filter must not guess a prefix.');
        } catch (\LogicException $exception) {
            self::assertStringContainsString('Cannot determine the WordPress table prefix', $exception->getMessage());
        }
    }
}
