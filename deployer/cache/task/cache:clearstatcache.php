<?php

namespace Deployer;

task('cache:clearstatcache', function () {
    run('{{bin/php}} -r "clearstatcache(true);if(function_exists(\'opcache_reset\')) opcache_reset();if(function_exists(\'eaccelerator_clear\')) eaccelerator_clear();"');
})->desc('Clear php realpath cache (clearstatcache)');
