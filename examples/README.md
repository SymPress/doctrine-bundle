# Native Doctrine example

Copy `Entity/` and `Repository/` into your application's PSR-4 directory and
`config/services.php` into its kernel-loaded configuration directory. This
example uses `App\\ => app/`; adjust both the Composer autoload and mapping path
for a different directory. Create the `migrations/` directory and set
`DATABASE_URL` through the application's environment.

For PostgreSQL, specify the platform version as part of the native DBAL
configuration, for example
`postgresql://user:password@host/database?serverVersion=17.0.0` for PostgreSQL 17.
The accepted database suite uses explicit platform selection for PostgreSQL.

Inject `EntityManagerInterface` and `RecordRepository` into an application
service. Persist with `$manager->persist(new Record('ORDER-42'))`, then
`$manager->flush()`. Query with `$repository->byReference('ORDER-42')`.
Use `wrapInTransaction()` for an atomic work unit on this connection.

Review `doctrine:migrations:diff`, then run `doctrine:migrations:migrate` during
deployment using the application's SymPress console. Production requests never
create schema. `SchemaTool` in the smoke test is confined to its disposable
acceptance database.

For optional Symfony bundles, install their components and explicitly register:

```php
return [
    SymPress\DoctrineBundle\Optional\SecurityBundle::class => ['all' => true],
    SymPress\DoctrineBundle\Optional\MakerBundle::class => ['dev' => true],
];
```

Keep Maker in `require-dev`. Configure Security's native `security` section and
Framework routing separately. This enables Doctrine entity user providers; it
does not authenticate WordPress users automatically. Enable Form, Validator,
Serializer, Messenger and profiling through their native framework settings.
Use `symfony/doctrine-messenger` for `doctrine_transaction` middleware and
Doctrine transports. No optional integration changes the WordPress login flow.
