# SymPress Doctrine Bundle

Native Doctrine ORM and DBAL integration for the SymPress ecosystem. Uses the
original DoctrineBundle, DoctrineMigrationsBundle and Symfony Doctrine Bridge.
Requires PHP 8.5 and SymPress Kernel >= 1.1.7. Entities and persistence do not depend on `wpdb` or WordPress
hooks. The package has no dependency on another SymPress persistence package.

## Installation

This is a private Composer library. Configure a read-only authenticated VCS
repository in the consuming application's root `composer.json`:

```json
{"repositories": [{"type": "vcs", "url": "git@github.com:SymPress/doctrine-bundle.git"}]}
```

Then install the stable release with `composer require sympress/doctrine-bundle:^1.0`.
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
`examples/`. See [the architecture and acceptance contract](docs/integration-concept.md)
and [feature coverage](docs/feature-matrix.md) before production adoption.

Optional Symfony integrations require their own components: Form, Validator,
SecurityBundle, MakerBundle or Doctrine Messenger. This package preserves the
upstream integration; it does not install a WordPress authentication layer.

## Database ownership

Use Doctrine for application-owned entities and tables. When sharing a database
with WordPress, configure a `schema_filter` for every connection used for schema
comparison, including the migrations metadata table. Review generated migrations
and execute them during deployment. Bundle boot never applies schema changes.

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
