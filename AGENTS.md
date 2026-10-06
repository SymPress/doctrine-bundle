# SymPress Doctrine Bundle

This package adapts the original DoctrineBundle and DoctrineMigrationsBundle to
the SymPress kernel. It has no dependency on the existing SymPress persistence
packages.

## Invariants

- Keep Doctrine ORM, DBAL, repository and mapping APIs upstream-native.
- Inherit or delegate upstream build, boot and shutdown behavior; do not copy service files,
  configuration trees, compiler passes or persistence code.
- Keep the native `doctrine` and `doctrine_migrations` configuration aliases.
- Never obtain a connection from WordPress globals or share `wpdb` handles.
- Never run schema changes during bundle discovery, boot or request handling.
- Preserve optional Symfony bridge integrations when their components exist.
- Database tests use disposable databases and must prove scope isolation.

## Verification

- Focused: `composer tests -- --filter <TestName>`.
- Full: `composer qa` (strict lint, coding standard, PHPStan and PHPUnit).
- Database: `DOCTRINE_TEST_DATABASE_URL=<disposable URL> composer tests:database`.
- Before v1/release: run hosted QA, current-dependency canary, native kernel boot,
  compiled-container reload, migration round trip and fresh source archive install.

Keep the README and examples aligned with public behavior and configuration.
Use signed release tags and pinned shared SymPress workflows.
