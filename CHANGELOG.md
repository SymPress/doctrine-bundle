# Changelog

## 1.0.1 — 2026-10-16

- Require the security-patched SymPress Kernel and Framework Bundle so fresh and
  lowest-version installations exclude vulnerable Symfony ExpressionLanguage,
  YAML and HttpFoundation releases.
- Update the development lock to Symfony 8.1.9 for ExpressionLanguage, YAML and
  HttpFoundation, including CVE-2026-109449 and the request-parser regression fix.

## 1.0.0 — 2026-10-06

- Native Doctrine ORM, DBAL, DoctrineBundle and DoctrineMigrationsBundle integration.
- Original Symfony Doctrine Bridge integrations and Symfony-compatible APIs.
- SymPress discovery adapters with upstream lifecycle and compiler-pass preservation.
- Strict shared QA, kernel integration coverage and database acceptance tests.
- Optional native SecurityBundle and MakerBundle adapters, entity providers,
  Messenger transaction middleware, form/validation and extensibility coverage.
- Production archive installation and real WordPress acceptance fixture.
- Require Kernel >= 1.1.7 to preserve listener state across container rebuilds.
