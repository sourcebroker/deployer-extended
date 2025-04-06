<?php

namespace Deployer;

use Deployer\Exception\GracefulShutdownException;

// Read more on https://github.com/sourcebroker/deployer-extended#service_php_fpm_reload
task('service:php_fpm_reload', function () {
    $command = get('service_php_fpm_reload_command');
    $shouldStopOnFailure = get('service_php_fpm_reload_stop_on_failure', false);

    if ($command !== null && $command !== '') {
        try {
            run($command);
            if (output()->isVerbose()) {
                writeln('PHP-FPM reloaded successfully.');
            }
        } catch (\Exception $e) {
            if ($shouldStopOnFailure) {
                throw new GracefulShutdownException('Error reloading PHP-FPM: ' . $e->getMessage());
            }
            writeln('Error reloading PHP-FPM: ' . $e->getMessage());
        }
    }
})->desc('Reload PHP-FPM service')->hidden();
