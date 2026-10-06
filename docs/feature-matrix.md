# v1 feature coverage

The bridge delegates to the original Doctrine and Symfony bundles. This matrix
distinguishes exercised integration contracts from features supplied by upstream
libraries. Optional components are development dependencies here and must be
installed explicitly by consumers that use them.

| Contract | Automated evidence |
| --- | --- |
| Original extensions, aliases, compiler passes and resource identity | `BundleContractTest` |
| Attribute entities, ORM CRUD, DQL, service repositories, registry and named managers | `KernelPersistenceTest` |
| Symfony `UniqueEntity` and `EntityType` | `KernelPersistenceTest` |
| Cascades, lazy collections, identity map and optimistic locking | `TransactionsAndLifecycleTest` |
| ORM rollback, closed-manager recovery, work-unit reset and connection shutdown | `TransactionsAndLifecycleTest` |
| Migrations up/down, metadata, native migration/mapping commands and foreign table schema scope | `MigrationsAndConsoleTest` |
| Native Security entity provider and UUID hydration | `SymfonyFeaturesTest` |
| Custom DBAL type, attribute listener, tagged logging middleware, DQL function and SQL filter | `SymfonyFeaturesTest` |
| Native entity argument resolver | `SymfonyFeaturesTest` |
| Messenger transaction middleware commit and handler-failure rollback | `SymfonyFeaturesTest` |
| Native Maker entity, repository and migration generation | `SymfonyFeaturesTest` |
| Compiled container reused by a fresh PHP process with persisted data | `ProductionContainerTest` |
| MariaDB and PostgreSQL persistence, relations, rollback, migration round trip and foreign data preservation | `DatabaseAcceptanceTest` plus database CI |
| Production dependencies from a source archive, Composer discovery, real WordPress boot and repository round trip with zero wpdb persistence queries | `tests/WordPress/run.sh` plus archive/WordPress CI |

The SQLite kernel fixture uses a minimal WordPress hook host and deliberately has
no `wpdb` class. The separate WordPress fixture loads real WordPress and observes
its database handle to prove that the Doctrine work unit does not use it.

Other native ORM facilities, including inheritance, embeddables, XML mappings,
custom ID generators, result caches, second-level caches and platform-specific
types, retain their original configuration and implementation. They are not each
independently acceptance-tested in v1. Profiler collectors retain upstream
registration; profiler UI, Symfony HTTP firewalls, async worker deployment and
application-specific tenant isolation require their respective host setup.

`Optional\\SecurityBundle` handles SymPress's extension-registration order so
Doctrine's native entity-provider factory works when Security is built after
Doctrine. Register one SecurityBundle host and one MakerBundle host per kernel;
do not register both a native bundle and its adapter for the same extension.

Kernel >= 1.1.7 isolates compiler passes across candidate container rebuilds.
This is required for stateful upstream passes such as Doctrine's listener pass.
The bridge contains no copied ORM, migration or container definitions.

See [release evidence](release-v1.0.0.md) for exact versions and executed checks.
