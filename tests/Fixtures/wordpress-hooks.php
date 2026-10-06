<?php

declare(strict_types=1);

// The kernel needs its hook host. Persistence deliberately has no wpdb host.
if (!function_exists('add_filter')) {
    function add_filter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        $GLOBALS['doctrine_fixture_hooks'][$hook][$priority][] = [$callback, $acceptedArgs];
        return true;
    }
}
if (!function_exists('add_action')) {
    function add_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        return add_filter($hook, $callback, $priority, $acceptedArgs);
    }
}
