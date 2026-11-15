<?php

declare(strict_types=1);

namespace SymPress\DoctrineBundle\Dbal;

use SymPress\Kernel\SiteConfig;

/**
 * Hides every table that starts with the WordPress table prefix from schema tools, so schema updates,
 * migration diffs and fixture purges never touch WordPress tables in a shared database.
 */
final class WordPressTablesSchemaFilter
{
    public function __construct(private readonly SiteConfig $config)
    {
    }

    // DBAL 4 passes asset names as strings, optionally qualified as "schema.table".
    public function __invoke(string $assetName): bool
    {
        return stripos((string) preg_replace('~^.*\.~', '', $assetName), $this->prefix()) !== 0;
    }

    private function prefix(): string
    {
        // phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable -- WordPress exposes it only so.
        [$wpdb, $tablePrefix] = [$GLOBALS['wpdb'] ?? null, $GLOBALS['table_prefix'] ?? null];
        // base_prefix also covers multisite site tables such as wp_2_posts.
        $prefix = match (true) {
            is_object($wpdb) && isset($wpdb->base_prefix) => $wpdb->base_prefix,
            $tablePrefix !== null => $tablePrefix,
            default => $this->config->get('DB_TABLE_PREFIX'),
        };
        // Fail closed: without a known prefix, any schema change could drop WordPress tables.
        if (!is_string($prefix) || $prefix === '') {
            throw new \LogicException(
                'Cannot determine the WordPress table prefix, so Doctrine schema tools are blocked to protect '
                . 'WordPress tables. Run them inside WordPress (e.g. "wp console") or set DB_TABLE_PREFIX.',
            );
        }

        return $prefix;
    }
}
