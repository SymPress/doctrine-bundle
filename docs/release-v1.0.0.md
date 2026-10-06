# v1.0.0 acceptance record

Release preparation: 2026-10-06. Hosted checks and archive acceptance are pending.

## Implemented and locally checked

- PHP 8.5.9; DoctrineBundle 3.3.2, DoctrineMigrationsBundle 4.0.1, ORM 3.7.4,
  DBAL 4.5.0, Symfony Doctrine Bridge 8.1.8, SymPress Kernel 1.1.7,
  Framework Bundle 1.0.5 and shared QA 0.1.2.
- Strict shared QA: syntax, PHPCS and PHPStan passed. PHPUnit: 15 successful
  tests, 124 assertions and one separate database-suite skip without a DSN.
- MariaDB 11.8 and PostgreSQL 17: one database test and 18 assertions each.
- Kernel's compiler-pass regression and full QA passed (78 tests, 372
  assertions). Existing environment skip and PHPUnit notices remain visible.
- Kernel fix is signed v1.1.7 at `910e5bbb4b30684c0cd5a8512ae0f6c47f1282e6`;
  public Packagist metadata and a fresh dependency update resolve that exact
  source. The accidental duplicate v1.1.6 is withdrawn and is not admitted by
  this package's minimum constraint.

## Repository limits

The repository is private as requested. GitHub's ruleset API returned HTTP 403
with a plan-upgrade requirement for private repositories. Main/tag protection
rules cannot be enforced under the current plan. QA, explicit release checks,
signed commits and signed tags remain part of the release process.

SSH signatures are checked locally against the existing SymPress release public
key (ED25519 fingerprint `SHA256:t63b+AEbAYq0XN04EJw/SAF3zsIdpzXoLyjlgsbD9ow`).
GitHub currently reports `unknown_key` for this signing key, so a GitHub Verified
badge is not claimed.

The weekly dependency check is configured and will be exercised manually before
release. A manual pass does not prove a future scheduled run. Application-level
deployments, backups and tenant isolation remain application acceptance work.
