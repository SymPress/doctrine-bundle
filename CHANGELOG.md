# Changelog

## 1.0.6 — 2026-11-16

- Keep the database password out of connect-failure stack traces. The mysqli
  driver middleware captured the `#[\SensitiveParameter]` connection parameters in
  a closure, where trace arguments still exposed them.
- Pin the shared `sympress/workflows` CI workflows to v1.3.2.

## 1.0.5 — 2026-11-15

- Never drop WordPress tables from Doctrine schema tools. Without a configured
  `schema_filter`, `doctrine:schema:update --force` and `doctrine:migrations:diff`
  followed by `migrate` dropped every WordPress table in a shared database. Every
  connection now hides tables that start with the WordPress table prefix
  (`$wpdb->base_prefix`, then `$table_prefix`, then `DB_TABLE_PREFIX`), on top of
  any configured `schema_filter`. Fixture purges skip them as well.
- Outside WordPress, set `DB_TABLE_PREFIX` before running schema tools, migrations
  or fixture purges: without a known prefix they now stop with an exception instead
  of guessing.

## 1.0.4 — 2026-11-15

- Make SQL errors on `mysqli` connections reliable inside WordPress. `wpdb` turns
  mysqli error reporting off for the whole process, so DBAL raised `TypeError`s
  instead of its exceptions and could ignore failed statements. A DBAL middleware
  now runs every mysqli driver call in strict reporting mode and restores the
  previous mode afterwards; other drivers are untouched.

## 1.0.3 — 2026-11-06

- Require the patched Framework Bundle and Kernel in downstream Composer installations.

## 1.0.2 — 2026-11-06

- Use the Symfony 8.1.11 security patches released on November 4.

## 1.0.1 — 2026-10-16

- Require the security-patched SymPress Kernel and Framework Bundle so fresh and
  lowest-version installations exclude vulnerable Symfony ExpressionLanguage,
  YAML and HttpFoundation releases.
- Update the development lock to Symfony 8.1.9 for ExpressionLanguage, YAML and
  HttpFoundation, including CVE-2026-109449 and the request-parser regression fix.
- Keep the security user fixture compatible with the declared lowest development
  dependencies when exercising the patched parser floors.

## 1.0.0 — 2026-10-06

- Native Doctrine ORM, DBAL, DoctrineBundle and DoctrineMigrationsBundle integration.
- Original Symfony Doctrine Bridge integrations and Symfony-compatible APIs.
- SymPress discovery adapters with upstream lifecycle and compiler-pass preservation.
- Strict shared QA, kernel integration coverage and database acceptance tests.
- Optional native SecurityBundle and MakerBundle adapters, entity providers,
  Messenger transaction middleware, form/validation and extensibility coverage.
- Production archive installation and real WordPress acceptance fixture.
- Require Kernel >= 1.1.7 to preserve listener state across container rebuilds.
