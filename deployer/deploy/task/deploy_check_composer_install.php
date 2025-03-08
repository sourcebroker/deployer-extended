<?php

namespace Deployer;

use Deployer\Exception\GracefulShutdownException;

// Read more on https://github.com/sourcebroker/deployer-extended#deploy-check-composer-install
task('deploy:check_composer_install', function () {
    if (file_exists(get('project_root') . '/composer.lock')) {
        $output = runLocally('{{local/bin/composer}} install ' .
            get('check_composer_install_options',
                '--verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader') .
            '  --dry-run 2>&1');
        if (strpos($output, 'Nothing to install') === false && strpos($output,
                'Package operations: 0 installs, 0 updates, 0 removals') === false) {
            throw new GracefulShutdownException('A composer.lock changes has been detected but you did not run "composer install". Please run composer install, then check if everything is working on your instance and do deploy after.');
        }
    }
})->desc('Check if composer install is needed before making deployment')->hidden();
