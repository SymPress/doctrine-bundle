<?php

declare(strict_types=1);

// Disposable acceptance host only. No ports or credentials for production.
define('WP_INSTALLING', true);
define('DB_NAME', 'sympress_doctrine_test');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', getenv('DOCTRINE_WORDPRESS_DB_HOST') ?: '127.0.0.1');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('WP_DEBUG', false);
define('WP_HOME', 'http://doctrine.example.test');
define('WP_SITEURL', 'http://doctrine.example.test');
define('DISABLE_WP_CRON', true);
define('WP_HTTP_BLOCK_EXTERNAL', true);
define('WP_ENVIRONMENT_TYPE', 'local');
define('AUTH_KEY', 'disposable-acceptance-host-only');
define('SECURE_AUTH_KEY', 'disposable-acceptance-host-only');
define('LOGGED_IN_KEY', 'disposable-acceptance-host-only');
define('NONCE_KEY', 'disposable-acceptance-host-only');
define('AUTH_SALT', 'disposable-acceptance-host-only');
define('SECURE_AUTH_SALT', 'disposable-acceptance-host-only');
define('LOGGED_IN_SALT', 'disposable-acceptance-host-only');
define('NONCE_SALT', 'disposable-acceptance-host-only');
$table_prefix = 'wp_';
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
require_once ABSPATH . 'wp-settings.php';
