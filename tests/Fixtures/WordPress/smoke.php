<?php

declare(strict_types=1);

use App\Entity\Record;
use App\Repository\RecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use SymPress\Kernel\App;
use SymPress\Kernel\Console\ConsoleApplicationFactory;
use SymPress\Kernel\Kernel\SiteKernel;

$root = getenv('DOCTRINE_CONSUMER_ROOT');
if (!is_string($root) || $root === '') {
    throw new RuntimeException('A disposable consumer root is required.');
}
require $root . '/vendor/autoload.php';
require $root . '/wordpress/wp-load.php';
require ABSPATH . 'wp-admin/includes/upgrade.php';
if (parse_url((string) getenv('DATABASE_URL'), PHP_URL_PATH) !== '/' . DB_NAME) {
    throw new RuntimeException('WordPress acceptance requires its dedicated disposable database.');
}
add_filter('pre_wp_mail', static fn (): bool => true);
if (!is_blog_installed()) {
    wp_install('Doctrine acceptance', 'doctrine-test', 'test@example.test', false, '', 'disposable-test-only');
}
update_option('sympress_doctrine_probe', 'preserved');
$kernel = new SiteKernel($root, 'prod', false);
App::new($kernel)->boot();
$container = $kernel->getContainer();
$registry = $container->get('doctrine');
if (!$registry instanceof ManagerRegistry) {
    throw new RuntimeException('Native public Doctrine registry is missing.');
}
$manager = $registry->getManager();
if (!$manager instanceof EntityManagerInterface) {
    throw new RuntimeException('Native entity manager alias is missing.');
}
$manager->getConnection()->executeStatement('DROP TABLE IF EXISTS enterprise_record');
(new SchemaTool($manager))->createSchema($manager->getMetadataFactory()->getAllMetadata());
/** @var wpdb $wpdb */
$wpdb = $GLOBALS['wpdb'];
$queries = $wpdb->num_queries;
$record = new Record('ARCHIVE-WORDPRESS');
$manager->persist($record);
$manager->flush();
$id = $record->getId();
$manager->clear();
$repository = $manager->getRepository(Record::class);
if (!$repository instanceof RecordRepository || $repository->byReference('ARCHIVE-WORDPRESS')?->getId() !== $id) {
    throw new RuntimeException('Archive repository round trip failed.');
}
if ($wpdb->num_queries !== $queries || get_option('sympress_doctrine_probe') !== 'preserved') {
    throw new RuntimeException('Doctrine persistence touched wpdb or damaged WordPress data.');
}
$sql = (new SchemaTool($manager))->getUpdateSchemaSql($manager->getMetadataFactory()->getAllMetadata());
if (str_contains(implode("\n", $sql), $wpdb->prefix)) {
    throw new RuntimeException('Schema comparison includes WordPress-owned tables.');
}
$factory = $container->get(ConsoleApplicationFactory::class);
if (!$factory instanceof ConsoleApplicationFactory) {
    throw new RuntimeException('Native console is missing.');
}
// The consumer has no development dependencies, including CommandTester.
$console = $factory->create();
if (!$console->has('doctrine:migrations:migrate') || !$console->has('doctrine:schema:validate')) {
    throw new RuntimeException('Native Doctrine commands are missing.');
}
echo json_encode([
    'wordpress' => $GLOBALS['wp_version'], 'archive' => true, 'wpdb_queries_for_doctrine' => 0,
], JSON_THROW_ON_ERROR);
$kernel->shutdown();
