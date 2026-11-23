# SymPress Doctrine Bundle

Native Doctrine ORM and DBAL integration for the SymPress ecosystem. Uses the
original DoctrineBundle, DoctrineMigrationsBundle and Symfony Doctrine Bridge.
Requires PHP 8.5, SymPress Kernel >= 1.1.14 and Framework Bundle >= 1.0.9.
Entities and persistence do not depend on `wpdb` or WordPress hooks. The package
has no dependency on another SymPress persistence package.

## Installation

Install the public package from Packagist in the consuming application's root:

```sh
composer require sympress/doctrine-bundle:^1.0.7
```

The SymPress kernel discovers the bundle through `extra.kernel` and registers its
Framework and Migrations requirements. Symfony Flex is not required.

## Configuration and use

Use native `doctrine` and `doctrine_migrations` configuration in your application's
kernel-loaded config directory. Configure entities explicitly by PSR-4 namespace
and path. Use `Doctrine\ORM\Mapping` attributes, inject
`Doctrine\ORM\EntityManagerInterface` into application services, and use
`Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository` for
service-backed repositories. Existing Symfony Doctrine documentation applies.

The complete [configuration example and usage](examples/README.md) are maintained in
`examples/`.

Optional Symfony integrations require their own components: Form, Validator,
SecurityBundle, MakerBundle or Doctrine Messenger. This package preserves the
upstream integration; it does not install a WordPress authentication layer.

## mysqli inside WordPress

WordPress' `wpdb` sets `mysqli_report(MYSQLI_REPORT_OFF)` for the whole PHP
process, while DBAL's `mysqli` driver relies on mysqli exceptions. The bundle
registers a DBAL middleware that runs each Doctrine call on a `mysqli`
connection in strict reporting mode and restores the previous mode afterwards,
so DBAL raises its usual exceptions and `wpdb` keeps its behavior. `pdo_mysql`
connections are not affected.

## Database ownership

Use Doctrine for application-owned entities and tables. On every connection the
bundle hides all tables that start with the WordPress table prefix from Doctrine's
schema tools, so `doctrine:schema:update`, `doctrine:migrations:diff` and the
fixtures purger never touch `wp_posts`, `wp_users` or multisite tables such as
`wp_2_posts`, even when an entity maps such a table or quotes its name. The
prefix comes from `$wpdb->base_prefix`, then the `$table_prefix` global, then
`DB_TABLE_PREFIX` (environment variable or constant). Without any of them, schema
tools refuse to run instead of guessing. This cannot be turned off.

Do not start application table names with the WordPress prefix: schema tools,
migration diffs and fixture purges skip them. A configured `schema_filter` still
applies on top; keep one that admits only your own tables and the migrations
metadata table.
`doctrine:migrations:diff --filter-expression` replaces all connection filters for
that run, including this one. `doctrine:database:drop --force` drops the whole
database, which WordPress shares; no schema filter applies to it, so do not run it
against a WordPress database. Review generated migrations and execute them during
deployment. Bundle boot never applies schema changes.

Separate `wpdb` and DBAL connections do not share a transaction. Modify WordPress
and WooCommerce-owned data through their APIs. Tenant selection and work-unit
reset belong to the application, not an implicit WordPress blog switch.

## Development

```bash
composer install
composer qa
DOCTRINE_TEST_DATABASE_URL='mysql://user:password@127.0.0.1/sympress_doctrine_test' composer tests:database
```

The default suite verifies the real SymPress kernel and original Doctrine stack
against SQLite. Hosted CI also runs disposable MariaDB and PostgreSQL tests.
Development dependencies are locked; consumers resolve stable runtime ranges.

License: GPL-2.0-or-later. Upstream dependencies retain their original licenses.
