#!/usr/bin/env bash
set -euo pipefail
package_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
archive_root="$(mktemp -d "${TMPDIR:-/tmp}/sympress-doctrine-archive-XXXXXX")"
mkdir "$archive_root/package" "$archive_root/consumer"
git -C "$package_root" archive HEAD | tar -x -C "$archive_root/package"
cp "$package_root/tests/WordPress/consumer-composer.json" "$archive_root/consumer/composer.json"
mkdir "$archive_root/consumer/app" "$archive_root/consumer/config" "$archive_root/consumer/migrations"
cp -R "$archive_root/package/examples/Entity" "$archive_root/package/examples/Repository" "$archive_root/consumer/app/"
cp "$archive_root/package/examples/config/services.php" "$archive_root/consumer/config/services.php"
composer install --working-dir="$archive_root/consumer" --no-dev --no-interaction --prefer-dist --no-progress
curl --fail --silent --show-error --location https://wordpress.org/wordpress-7.1.3.tar.gz -o "$archive_root/wordpress.tar.gz"
tar -xzf "$archive_root/wordpress.tar.gz" -C "$archive_root/consumer"
cp "$package_root/tests/Fixtures/WordPress/wp-config.php" "$archive_root/consumer/wordpress/wp-config.php"
export DOCTRINE_CONSUMER_ROOT="$archive_root/consumer"
export DATABASE_URL="mysql://root@${DOCTRINE_WORDPRESS_DB_HOST:-127.0.0.1}/sympress_doctrine_test"
php "$package_root/tests/Fixtures/WordPress/smoke.php"
printf '\nArchive consumer retained for inspection: %s\n' "$archive_root"
