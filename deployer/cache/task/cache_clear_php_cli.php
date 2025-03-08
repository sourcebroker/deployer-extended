<?php

namespace Deployer;

// Read more on https://github.com/sourcebroker/deployer-extended#cache-clear-php-cli
task('cache:clear_php_cli', function () {
    run('{{bin/php}} -r "clearstatcache(true);if(function_exists(\'opcache_reset\')) opcache_reset();if(function_exists(\'eaccelerator_clear\')) eaccelerator_clear();"');
})->desc('Clear php cli caches (clearstatcache, opcache_reset, eaccelerator_clear)')->hidden();
