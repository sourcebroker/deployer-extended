<?php

namespace Deployer;

// Read more on https://github.com/sourcebroker/deployer-extended#cache-clear-php-cli
task('cache:clear_php_cli', function () {
    run('{{bin/php}} -r "clearstatcache(true);if(function_exists(\'opcache_reset\')) opcache_reset();"');
})->desc('Clear php cli caches (clearstatcache, opcache_reset)')->hidden();
